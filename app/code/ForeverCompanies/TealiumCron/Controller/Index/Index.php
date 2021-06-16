<?php
namespace ForeverCompanies\TealiumCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\TealiumCron\Controller\Index\S3;
use Magento\Framework\Filesystem\Io\File;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Catalog\Model\ProductFactory;

use ForeverCompanies\TealiumCron\Model\Event;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\File\Csv;


require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/ForeverCompanies_Pid.php';
require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/S3.php';


const RESULTS_PER_PAGE = 500;

// site variables needed to be included in Tealium feed
const SITE_VARS = [
    5 => [
        'site_name' => ['diamondnexus', 'www'],
        'brand' => 'Diamond Nexus',
        'parent_store' => 1,
    ],
    14 => [
        'site_name' => ['foreverartisans', 'www'],
        'brand' => 'Forever Artisans',
        'parent_store' => 11,
    ],
    17 => [
        'site_name' => ['1215diamonds', 'www'],
        'brand' => '1215 Diamonds',
        'parent_store' => 12,
    ],
];

const FOREVER_ARTISANS_STORE_ID = 11;

// the default store to use if no other store is found
const DEFAULT_PARENT_STORE = 1;

// order type to be passed to Tealium
const ORDER_TYPE = 'call center';

// header fields for csv
const CSV_HEADERS = [
    'site_section', // required
    'tealium_event', // Required
    'page_name', // Required
    'language_code', // Suggested
    'customer_email', // Required
    'order_id', // Required
    'customer_id', // Required
    'customer_first_name', // Required
    'customer_last_name', // Required
    'customer_street_line1', // Suggested
    'customer_street_line2', // Suggested
    'customer_city', // Required
    'customer_state', // Required
    'customer_zip', // Required
    'customer_country', // Required
    'country_code', // Required
    'customer_first_name_shipping', // Required
    'customer_last_name_shipping', // Required
    'customer_street_line1_shipping', // Suggested
    'customer_street_line2_shipping', // Suggested
    'customer_city_shipping', // Required
    'customer_state_shipping', // Required
    'customer_zip_shipping', // Required
    'customer_country_shipping', // Required
    'order_type', // Required
    'order_created_date', // Suggested
    'order_store', // Required
    'order_payment_type', // Required
    'order_shipping_type', // Required
    'order_promo_code', // Required
    'order_currency_code', // Required
    'order_merchandise_total', // Required
    'order_subtotal', // Required
    'order_shipping_amount', // Required
    'order_tax_amount', // Required
    'order_discount_amount', // Required
    'store_credit_payment_amt', // Suggested
    'order_total', // Required
    'product_brand', // Required
    'product_category', // Required
    'product_discount_amount', // Suggested
    'product_id', // Required
    'product_image_url', // Required
    'product_name', // Required
    'product_original_price', // Required
    'product_price', // Required
    'product_promo_code', // Required
    'product_quantity', // Required
    'product_sku', // Required
    'product_subcategory', // Required
    'product_url', // Suggested
    'site_name', // Required
    'product_simple_id', // Suggested
];

// max number of rows per csv file
const MAX_ROWS_PER_FILE = 100000;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $logger;
    
    protected $orderCollectionFactory;
    protected $eventModel;
    
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionF,
        Event $ev
        ) {
            $this->logger = $logger;
            $this->orderCollectionFactory = $orderCollectionF;
            $this->eventModel = $ev;
            
            parent::__construct($context);
    }
    
    
    public function execute()
    {
        echo 'ffff';die;
        
        // include PID class and check if process already running; if so, kill script
        // require_once '/var/www/magento/lib/ForeverCompanies/Pid.php';
        try {
            $pid = new ForeverCompanies_Pid(
                basename(__FILE__, '.php'),
                '/var/www/magento/var/locks/'
                );
            if ($pid->alreadyRunning) {
                die('The script ' . __FILE__ . ' is already running. Halting execution.');
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
        
        $orders = $this->orderCollectionFactory->create()
        ->addFieldToSelect('entity_id')
        ->addAttributeToFilter('store_id', ['in' => [5, 14, 17]])
        ->addAttributeToFilter('created_at', ['gteq' => '2013-09-01 00:00:00']) // orders prior have some issues
        ->addFieldToFilter(
            ['total_refunded', 'total_refunded'],
            [
                ['eq' => 0],
                ['null' => true]
            ]
            )
            ->addAttributeToFilter('state', ['nin' => ['canceled', 'holded']])
            ->addAttributeToSort('created_at', 'asc')
            ->setPageSize(RESULTS_PER_PAGE);
            
            // join to the tealium event table to exclude any sales order that already exists in that table
            $orders->getSelect()
            ->joinLeft(
                ['event' => 'forevercompanies_tealium_event'],
                'main_table.entity_id = event.entity_id AND event.entity_type = "sales_order"',
                ['id']
                )
                ->where('
            (`main_table`.`total_paid` = 0 AND `main_table`.`total_due` = 0)
            OR (
                (`main_table`.`total_due` = 0 OR `main_table`.`total_due` IS NULL)
                AND `main_table`.`total_paid` = `main_table`.`grand_total`
            )
            ')
            ->where('event.id is null');
            
            
            $orders->load();
            
            
            
            // get total number of pages in collection
            $pages = $orders->getLastPageNumber();
            
            // loop through each page, starting with page 1
            for ($i = 1; $i <= $pages; $i++) {
                //$orders->setCurPage($curPage);
                
                foreach ($orders as $order) {
                    try {
                       
                         
                         // Set the transaction data
                        $this->eventModel->setData([
                         'event' => 'order',
                         'entity_id' => $order->getEntityId(),
                         'entity_type' => 'sales_order',
                         ]);
                         
                         //Mage::log("i= " . $i . ", entityId=" . $order->getEntityId(), null, 'tealium-cron.log');
                         
                         // Save the transaction
                        $this->eventModel->save();
                        
                       
                    } catch (Exception $e) {
                        $this->logger->info($e->getMessage());
                    }
                }
                
                // make the collection unload the data in memory so it will pick up the next page when load() is called.
                $orders->clear();
            }
            
            
            
            
    }
	
}