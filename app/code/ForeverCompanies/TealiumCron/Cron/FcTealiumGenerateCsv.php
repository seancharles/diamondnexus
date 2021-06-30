<?php

namespace ForeverCompanies\TealiumCron\Cron;

use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Filesystem\Io\File;

use ForeverCompanies\TealiumCron\Model\Event;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\File\Csv;


require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/ForeverCompanies_Pid.php';
require_once '/var/www/magento/app/code/ForeverCompanies/TealiumCron/Controller/Index/S3.php';


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

class FcTealiumGenerateCsv
{   
    protected $ioFile;
    protected $logger;
    
    protected $orderFactory;
    protected $productFactory;
    
    protected $eventModel;
    protected $storeManager;
    
    protected $categoryCollectionFactory;
    protected $configurableProduct;
    protected $csvProcessor;
    
    public function __construct(
        LoggerInterface $logger,
        File $ioF,
        OrderInterfaceFactory $orderInterfaceF,
        ProductFactory $productF,
        Event $event,
        StoreManagerInterface $storeManagerI,
        CategoryCollectionFactory $categoryCollectionF,
        ConfigurableProduct $configurableP,
        Csv $cs
        ) {
            $this->logger = $logger;
            $this->ioFile = $ioF;
            
            $this->orderFactory = $orderInterfaceF;
            $this->productFactory = $productF;
            
            $this->eventModel = $event;
            
            $this->storeManager = $storeManagerI;
            $this->categoryCollectionFactory = $categoryCollectionF;
            $this->configurableProduct = $configurableP;
            $this->csvProcessor = $cs;
            
    }
    
    public function execute()
    {
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
        
        
        // array to store categories, by store id
        $storeCategories = [];
        
        // base output directory
        $baseDirectory = '/var/www/magento' . DS . 'export' . DS . 'call_center_orders';
        
        
        // staging output directory
        $stagingDirectory = $baseDirectory . DS . 'staging';
        
        // production queue directory for files to be transferred to tealium
        $queueDirectory = $baseDirectory . DS . 'queue';
        
        // set filename
        $filename = $this->generateFilename();
        
        
        /**
         * ========================================================
         * Begin script...
         * ========================================================
         */
        
        // include extension helper example (not implemented yet)
        //$csvHelper = Mage::helper('forevercompanies_tealium/csv');
        //var_dump($csvHelper->getMaxRowsPerFile());
        //die;
        
        try {
            
            // first verify/create our staging and queue directories
            
            $this->ioFile->checkAndCreateFolder($stagingDirectory);
            $this->ioFile->checkAndCreateFolder($queueDirectory);
            
            $orders = $this->eventModel
            ->getCollection()
            ->addFieldToSelect('id', 'event_id')
            ->addFieldToFilter('pushed_to_tealium', ['eq' => 0])
            ->setPageSize(RESULTS_PER_PAGE);
            
            
            
            $orders->getSelect()
            ->joinleft(
                ['orders' => 'sales_order'],
                'main_table.entity_id = orders.entity_id',
                [
                    'order_id' => 'increment_id',
                    'store_id' => 'store_id',
                    'customer_id' => 'customer_id',
                    'order_subtotal' => 'subtotal',
                    'order_shipping' => 'shipping_amount',
                    'order_tax' => 'tax_amount',
                    'order_discount_amount' => 'discount_amount',
                    'store_credit_payment_amt' => 'customer_balance_amount',
                    'order_grand_total' => 'grand_total',
                    'order_currency_code' => 'order_currency_code',
                    'order_promo_code' => 'coupon_code',
                    'customer_email' => 'customer_email',
                    'created_at' => 'created_at'
                ]
                )
                ->where('main_table.entity_type = "sales_order"');
                
                // join order billing address
                $orders->getSelect()->joinLeft(
                    ['address' => 'sales_order_address'],
                    'main_table.entity_id = address.parent_id and address.address_type = "billing"',
                    [
                        'customer_first_name' => 'firstname',
                        'customer_last_name' => 'lastname',
                        'customer_street_line1' => 'street',
                        'customer_city' => 'city',
                        'customer_state' => 'region',
                        'customer_zip' => 'postcode',
                        'customer_country' => 'country_id',
                    ]
                    );
                
                // join order billing address
                $orders->getSelect()->joinLeft(
                    ['shipping_address' => 'sales_order_address'],
                    'main_table.entity_id = shipping_address.parent_id and shipping_address.address_type = "shipping"',
                    [
                        'shipping_first_name' => 'firstname',
                        'shipping_last_name' => 'lastname',
                        'shipping_street_line1' => 'street',
                        'shipping_city' => 'city',
                        'shipping_state' => 'region',
                        'shipping_zip' => 'postcode',
                        'shipping_country' => 'country_id',
                    ]
                    );
                
                // load the collection
                $orders->load();
                
                //      echo 'the count here is ' . count($orders);die;
                
                // get number of pages being returned
                $pages = $orders->getLastPageNumber();
                
                // data to be written to csv
                $data = [CSV_HEADERS];
                
                // events processed and skipped
                $eventsProcessed = [];
                $eventsSkipped = [];
                
                // loop through each page, starting with page 1
                for ($i = 1; $i <= $pages; $i++) {
                    
                    // set the current page to be parsed
                    $orders->setCurPage($i);
                    
                    // loop through each order
                    foreach ($orders as $order) {
                        
                        // get global site info
                        $siteInfo = array_key_exists($order->getStoreId(), SITE_VARS) ? SITE_VARS[$order->getStoreId()] : [];
                        
                        // get the parent store id
                        $parentStoreId = array_key_exists('parent_store', $siteInfo)
                        ? $siteInfo['parent_store']
                        : DEFAULT_PARENT_STORE;
                        
                        $store = $this->storeManager->getStore($parentStoreId);
                        
                        /*
                         echo $parentStoreId;die;
                         
                         // get store object based on order
                         $store = Mage::app()->getStore($parentStoreId);
                         */
                        
                        
                        
                        // get site name
                        $siteName = array_key_exists('site_name', $siteInfo)
                        ? $siteInfo['site_name']
                        : '';
                        
                        // get order object
                        $orderObj = $this->orderFactory->create()->loadByIncrementId($order->getOrderId());
                        
                        // get payment type
                        $paymentType = $orderObj->getPayment()->getMethodInstance()->getTitle();
                        //$methodInstance = $payment->getMethodInstance();
                        //$methodTitle = $methodInstance->getTitle();
                        //$method = $payment->getMultipayPaymentMethod();
                        //$orderPaymentType = DiamondNexus_Multipay_Model_Constant::MULTIPAY_METHOD_LABEL[$method]; // order_payment_type
                        
                        // get shipping type
                        $shippingType = $orderObj->getShippingDescription();
                        
                        // get store credit amount applied
                        $storeCredit = $order->getStoreCreditPaymentAmt();
                        if ($storeCredit > 0) {
                            $storeCredit *= -1;
                        }
                        
                        // get all order items
                        $items = $orderObj->getAllVisibleItems();
                        
                        // arrays to hold our item data
                        $productId = [];
                        $productSku = [];
                        $productName = [];
                        $productOriginalPrice = [];
                        $productPrice = [];
                        $productQuantity = [];
                        $productDiscountAmount = [];
                        $productBrand = [];
                        $productCategory = [];
                        $productSubcategory = [];
                        $productImageUrl = [];
                        $productUrl = [];
                        $productPromoCode = [];
                        $productSimpleId = [];
                        
                        // get store categories if they haven't already been retrieved
                        if (!array_key_exists($order->getStoreId(), $storeCategories)) {
                            $storeCategories[$order->getStoreId()] = $this->getCategories($order->getStoreId());
                        }
                        
                        // our current array key
                        $x = 0;
                        
                        // merchandise subtotal
                        $merchandiseSubtotal = 0.0;
                        
                        // loop through each item and populate our data
                        if (sizeof($items) > 0) {
                            foreach ($items as $item) {
                                
                                // get the current product
                                $product = $this->productFactory->create()->load($item->getProductId());
                                $product->setStoreId($parentStoreId);
                                
                                // should this item be excluded from tealium?
                                $excludeFromTealium = $product->getData('exclude_from_tealium');
                                if ($excludeFromTealium) {
                                    continue;
                                }
                                
                                // determine if the product is configurable
                                // if so, grab the configurable product record as primary product
                                if ($product->getTypeId() == 'simple') {
                                    $configurableProduct = $this->configurableProduct
                                    ->getParentIdsByChild($item->getProductId());
                                    
                                    
                                    $productConfigurableId = null;
                                    if (!empty($configurableProduct)) {
                                        $productConfigurableId = $configurableProduct[0];
                                    }
                                    
                                    if (!empty($productConfigurableId)) {
                                        $primaryProduct = $this->productFactory->create()->load($productConfigurableId);
                                        $primaryProduct->setStoreId($parentStoreId);
                                    } else {
                                        $primaryProduct = $product;
                                    }
                                } else {
                                    $primaryProduct = $product;
                                }
                                
                                // get primary category for tealium
                                // - 2020/04/13: for now, not using this field)
                                //$tealiumCategory = $product->getAttributeText('feed_category');
                                
                                // get categories and subcategories
                                $productCats = [];
                                foreach ($primaryProduct->getCategoryIds() as $categoryId) {
                                    if (isset($storeCategories[$order->getStoreId()][$categoryId])) {
                                        $levelType = ($storeCategories[$order->getStoreId()][$categoryId]['navlevel'] == 2)
                                        ? 'category'
                                            : 'subcategory';
                                            $productCats[$levelType][] = $storeCategories[$order->getStoreId()][$categoryId];
                                    }
                                }
                                
                                $productCategories = [];
                                
                                if (isset($productCats['category'])) {
                                    foreach ($productCats['category'] as $c) {
                                        $productCategories[] = $c['slug'];
                                    }
                                }
                                
                                $productSubcategories = [];
                                
                                
                                if( isset($productCats['subcategory'])) {
                                    foreach ($productCats['subcategory'] as $c) {
                                        $productSubcategories[] = $c['slug'];
                                    }
                                }
                                
                                
                                // get product image
                                $productImg = !empty($primaryProduct->getImageUrl()) ? $primaryProduct->getImageUrl() : '';
                                
                                // get product brand
                                $brand = array_key_exists('brand', $siteInfo)
                                ? $siteInfo['brand']
                                : '';
                                
                                // build product URL:
                                $pUrl = ($parentStoreId == FOREVER_ARTISANS_STORE_ID)
                                ? ""
                                    : $store->getBaseUrl() . $primaryProduct->getUrlKey();
                                    
                                    // sum up merchandise total
                                    $merchandiseSubtotal += ($this->formatFloat($item->getPrice()) * $this->formatFloat($item->getQtyOrdered(), 0));
                                    
                                    // assign data to our variables
                                    $productId[$x] = $primaryProduct->getId();
                                    $productSimpleId[$x] = $item->getProductId();
                                    $productSku[$x] = $item->getSku();
                                    $productName[$x] = $item->getName();
                                    $productOriginalPrice[$x] = $this->formatFloat($item->getOriginalPrice());
                                    $productPrice[$x] = $this->formatFloat($item->getPrice());
                                    $productQuantity[$x] = $this->formatFloat($item->getQtyOrdered(), 0);
                                    $productDiscountAmount[$x] = $this->formatFloat($item->getDiscountAmount());
                                    $productBrand[$x] = $brand;
                                    $productCategory[$x] = is_array($productCategories) && array_key_exists(0, $productCategories)
                                    ? $productCategories[0]
                                    : '';
                                    $productSubcategory[$x] = is_array($productSubcategories) && array_key_exists(0, $productSubcategories)
                                    ? $productSubcategories[0]
                                    : '';
                                    $productImageUrl[$x] = $productImg;
                                    $productUrl[$x] = !empty($pUrl) ? $pUrl : '';
                                    $productPromoCode[$x] = "";
                                    
                                    // increment array key
                                    $x++;
                            }
                        }
                        
                        // if there is at least one product being added, go ahead and add to our data array
                        if ($x > 0) {
                            $data[] = [
                                'checkout', // site_section, Required
                                'order', // tealium_event, Required
                                'order', // page_name, Required
                                "", // language_code, Suggested
                                $this->formatString($order->getCustomerEmail()), // customer_email, Required
                                $order->getOrderId(), // order_id, Required
                                $order->getCustomerId(), // customer_id, Required
                                $this->formatString($order->getCustomerFirstName()), // customer_first_name, Required
                                $this->formatString($order->getCustomerLastName()), // customer_last_name, Required
                                $this->formatString($order->getCustomerStreetLine1()), // customer_street_line1
                                '', // customer_street_line2
                                $this->formatString($order->getCustomerCity()), // customer_city, Required
                                $this->formatString($order->getCustomerState()), // customer_state, Required
                                $this->formatString($order->getCustomerZip()), // customer_zip, Required
                                $this->formatString($order->getCustomerCountry()), // customer_country, Required
                                $this->formatString($order->getCustomerCountry()), // country_code, Required
                                $this->formatString($order->getShippingFirstName()), // customer_street_line1_shipping
                                $this->formatString($order->getShippingLastName()), // customer_street_line2_shipping
                                $this->formatString($order->getShippingStreetLine1()), // customer_street_line1_shipping
                                '', // customer_street_line2_shipping
                                $this->formatString($order->getShippingCity()), // customer_city_shipping
                                $this->formatString($order->getShippingState()), // customer_state_shipping
                                $this->formatString($order->getShippingZip()), // customer_zip_shipping
                                $this->formatString($order->getShippingCountry()), // customer_country_shipping
                                ORDER_TYPE, // order_type, Required
                                $order->getCreatedAt(), // order_created_date
                                $order->getStoreId(), // order_store, Required
                                $paymentType, // order_payment_type, Required
                                $shippingType, // order_shipping_type, Required
                                $order->getOrderPromoCode(), // order_promo_code, Required
                                $order->getOrderCurrencyCode(), // order_currency_code, Required
                                $this->formatFloat($merchandiseSubtotal), // order_merchandise_total, Required
                                $this->formatFloat($order->getOrderSubtotal()), // order_subtotal, Required
                                $this->formatFloat($order->getOrderShipping()), // order_shipping_amount, Required
                                $this->formatFloat($order->getOrderTax()), // order_tax_amount, Required
                                $this->formatFloat($order->getOrderDiscountAmount()), // order_discount_amount, Required
                                $this->formatFloat($storeCredit), // store_credit_payment_amt, Suggestion
                                $this->formatFloat($order->getOrderGrandTotal()), // order_total, Required
                                $this->formatArray($productBrand), // product_brand, Required
                                $this->formatArray($productCategory, 'string', true, true), // product_category, Required
                                $this->formatArray($productDiscountAmount, 'float'), // product_discount_amount, Suggested
                                $this->formatArray($productId, 'int'), // product_id, Required
                                $this->formatArray($productImageUrl), // product_image_url, Required
                                $this->formatArray($productName), // product_name, Required
                                $this->formatArray($productOriginalPrice, 'float'), // product_original_price, Required
                                $this->formatArray($productPrice, 'float'), // product_price, Required
                                $this->formatArray($productPromoCode), // product_promo_code, Required
                                $this->formatArray($productQuantity, 'int'), // product_quantity, Required
                                $this->formatArray($productSku), // product_sku, Required
                                $this->formatArray($productSubcategory, 'string', true, true), // product_subcategory, Required
                                $this->formatArray($productUrl), // product_url, Suggested
                                $this->formatArray($siteName, 'string', true, true), // site_name, Required
                                $this->formatArray($productSimpleId, 'int'), // product_simple_id, Suggested
                            ];
                            
                            $eventsProcessed[] = $order->getEventId();
                        } else {
                            $eventsSkipped[] = $order->getEventId();
                        }
                        
                        if (count($data) >= MAX_ROWS_PER_FILE) {
                            // create file
                            $this->createFile($filename, $data, $stagingDirectory, $queueDirectory);
                            
                            // generate new filename
                            $filename = generateFilename();
                            
                            // reset our data array
                            $data = [CSV_HEADERS];
                        }
                    }
                    
                    // make the collection unload the data in memory so it will pick up the next page when load() is called.
                    $orders->clear();
                }
                
                // only write out a file if we've processed more than 1 row (the header is our first row)
                if (count($data) > 1) {
                    $this->createFile($filename, $data, $stagingDirectory, $queueDirectory);
                }
                
                // update our event records
                // pushed_to_tealium field / 1 = sent / 2 = skipped or not set
                foreach ($eventsProcessed as $event) {
                    $this->updateEvent($event, 1);
                }
                foreach ($eventsSkipped as $event) {
                    $this->updateEvent($event, 2);
                }
                
                //    // set pushed_to_tealium field / 1 = sent / 2 = skipped or not set
                //    $pushedToTealium = ($x > 0) ? 1 : 2;
                //
                //    // update the event table to mark this row as being pushed to tealium
                //    $eventData = ['pushed_to_tealium' => $pushedToTealium, 'date_pushed' => date('Y-m-d H:i:s')];
                //    $event = Mage::getModel('forevercompanies_tealium/event')
                //        ->load($order->getEventId())
                //        ->addData($eventData);
                //    $event->setId($order->getEventId())->save();
                
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
        
        
    }
    
    /**
     * ========================================================
     * Utility functions
     * ========================================================
     */
    
    
    /**
     * Updates the events table with a processed or skipped value
     *
     * @param $eventId
     * @param $pushedToTealium 1 = processed, 2 = skipped
     * @throws Exception
     */
    function updateEvent($eventId, $pushedToTealium)
    {
        $eventData = ['pushed_to_tealium' => $pushedToTealium, 'date_pushed' => date('Y-m-d H:i:s')];
        $event = $this->eventModel
        ->load($eventId)
        ->addData($eventData);
        $event->setId($eventId)->save();
    }
    
    /**
     * Creates a CSV file of data array
     *
     * @param $filename
     * @param $data
     * @param $stagingDirectory
     * @param $queueDirectory
     */
    function createFile($filename, $data, $stagingDirectory, $queueDirectory)
    {
        // create file in staging directory
        
        
        echo $stagingDirectory . '<br />';
        echo $queueDirectory; die;
        
        $this->csvProcessor
        ->setEnclosure('"')
        ->saveData($stagingDirectory . DS . $filename, $data);
        
        // file created, now move to queue for processing
        rename($stagingDirectory . DS . $filename, $queueDirectory . DS . $filename);
    }
    
    /**
     * Removes tabs and newlines from a string
     *
     * @param $string
     * @return string
     */
    function formatString($string)
    {
        $str = str_replace("\t", " ", $string);
        $str = str_replace("\r\n", " ", $str);
        return trim(str_replace("\n", " ", $str));
    }
    
    /**
     * Returns a float formatted with defined number of decimal places
     *
     * @param $amount
     * @param int $decimals
     * @return string
     */
    function formatFloat($amount, $decimals = 2) {
        return number_format($amount, $decimals, ".", "");
    }
    
    /**
     * Formats an array of data for use in csv file
     *
     * @param $array
     * @param string $dataType
     * @param bool $makeLowerCase
     * @param bool $removeSpaces
     * @return string
     */
    function formatArray($array, $dataType = 'string', $makeLowerCase = false, $removeSpaces = false)
    {
        if ($makeLowerCase) {
            $array = array_map('strtolower', $array);
        }
        if ($removeSpaces) {
            foreach ($array as &$arr) {
                $arr = str_replace(' ', '_', $arr);
            }
        }
        
        switch ($dataType) {
            case 'int':
            case 'float':
                return '[' . implode(', ', $array) . ']';
                break;
                
            case 'string':
            default:
                // surround string pieces with double quotes, required for csv output
                return $this->formatString('["' . implode('", "', $array) . '"]');
                break;
        }
    }
    
    /**
     * Trims and converts string to lower case
     *
     * @param $string
     * @return string
     */
    function makeLowerCase($string) {
        return trim(strtolower($string));
    }
    
    /**
     * Converts spaces to underscores in a string
     *
     * @param $string
     * @return string|string[]
     */
    function removeSpacesFromString($string) {
        return str_replace(' ', '_', $string);
    }
    
    /**
     * Get all product categories
     *
     * @param $storeId
     * @return array
     */
    function getCategories($storeId) {
        $collection = $this->categoryCollectionFactory->create()->setStoreId($storeId)
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('is_active', 1)
        ->load();
        
        $dnCats = [];
        foreach ($collection as $c) {
            // Get 'navlevel' to reflect visual navigation to be able to separate cats and subcats for tealium
            // Notes: Upgrading Wedding bands & loose stones and degrading clearance & gifts to subcats
            $navLevel = $c->getLevel();
            if (in_array($c->getId(), [560,40])) {
                $navLevel--;
            }
            if (in_array($c->getId(), [425,860])) {
                $navLevel++;
            }
            $dnCats[$c->getId()] = [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'slug' => $this->formatSlug($c->getName()),
                'level' => $c->getLevel(),
                'navlevel' => $navLevel
            ];
        }
        
        return $dnCats;
    }
    
    /**
     * Formats a string to a slug
     *
     * @param $str
     * @return string|string[]|null
     */
    function formatSlug($str) {
        $output = trim(strtolower($str));
        $output = str_replace(' ', '_', $output);
        $output = preg_replace("/[^a-z0-9_]+/i", "", $output);
        return $output;
    }
    
    function generateFilename()
    {
        return 'callcenterorders_' . date('Ymd-His') . '.csv';
    }
}