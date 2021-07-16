<?php
namespace ForeverCompanies\TealiumCron\Cron;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\TealiumCron\Controller\Index\S3;
use ForeverCompanies\TealiumCron\Model\Event;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/ForeverCompanies_Pid.php';
require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/S3.php';

const RESULTS_PER_PAGE = 500;

class FcTealiumPopulateQueue
{   
    
    protected $logger;
    protected $orderCollectionFactory;
    protected $eventModel;
    protected $scopeConfig;
    protected $storeScope;
    
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionF,
        Event $ev,
        ScopeConfigInterface $scopeC
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionF;
        $this->eventModel = $ev;
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
    
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/tealium/fc_tealium_populate_queue', $this->storeScope)) {
            return $this;
        }
        
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