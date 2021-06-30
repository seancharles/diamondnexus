<?php
/**
 * This script will fetch various entities that need to be pushed over to Tealium and insert them into a queue table
 * for future processing
 */

// include Mage app
// require_once $_SERVER['HOME'] . 'magento//Mage.php';
umask(0);

// set current store to admin store id
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// include PID class and check if process already running; if so, kill script
require_once $_SERVER['HOME'] . '/html/lib/ForeverCompanies/Pid.php';
try {
    $pid = new ForeverCompanies_Pid(
        basename(__FILE__, '.php'),
        $_SERVER['HOME'] . '/html/var/locks/'
    );
    if ($pid->alreadyRunning) {
        die('The script ' . __FILE__ . ' is already running. Halting execution.');
    }
} catch (Exception $e) {
    Mage::logException($e);
    die;
}

/**
 * ========================================================
 * Configuration Variables
 * ========================================================
 */

// max page size
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

// array to store categories, by store id
$storeCategories = [];

// base output directory
$baseDirectory = Mage::getBaseDir('var') . DS . 'export' . DS . 'call_center_orders';

// staging output directory
$stagingDirectory = $baseDirectory . DS . 'staging';

// production queue directory for files to be transferred to tealium
$queueDirectory = $baseDirectory . DS . 'queue';

// set filename
$filename = generateFilename();

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
    $io = new Varien_Io_File();
    $io->checkAndCreateFolder($stagingDirectory);
    $io->checkAndCreateFolder($queueDirectory);

    $orders = Mage::getModel('forevercompanies_tealium/event')
        ->getCollection()
        ->addFieldToSelect('id', 'event_id')
        ->addFieldToFilter('pushed_to_tealium', ['eq' => 0])
        ->setPageSize(RESULTS_PER_PAGE);

    $orders->getSelect()
        ->joinleft(
            ['orders' => Mage::getSingleton('core/resource')->getTableName('sales/order')],
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
        ['address' => Mage::getSingleton('core/resource')->getTableName('sales/order_address')],
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
        ['shipping_address' => Mage::getSingleton('core/resource')->getTableName('sales/order_address')],
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

            // get store object based on order
            $store = Mage::app()->getStore($parentStoreId);

            // get site name
            $siteName = array_key_exists('site_name', $siteInfo)
                ? $siteInfo['site_name']
                : '';

            // get order object
            $orderObj = Mage::getModel("sales/order")->loadByIncrementId($order->getOrderId());

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
                $storeCategories[$order->getStoreId()] = getCategories($order->getStoreId());
            }

            // our current array key
            $x = 0;

            // merchandise subtotal
            $merchandiseSubtotal = 0.0;

            // loop through each item and populate our data
            if (sizeof($items) > 0) {
                foreach ($items as $item) {

                    // get the current product
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $product->setStoreId($parentStoreId);

                    // should this item be excluded from tealium?
                    $excludeFromTealium = $product->getData('exclude_from_tealium');
                    if ($excludeFromTealium) {
                        continue;
                    }

                    // determine if the product is configurable
                    // if so, grab the configurable product record as primary product
                    if ($product->getTypeId() == 'simple') {
                        $configurableProduct = Mage::getResourceSingleton('catalog/product_type_configurable')
                            ->getParentIdsByChild($item->getProductId());
                        $productConfigurableId = null;
                        if (is_array($configurableProduct)) {
                            $productConfigurableId = $configurableProduct[0];
                        }

                        if (!empty($productConfigurableId)) {
                            $primaryProduct = Mage::getModel('catalog/product')->load($productConfigurableId);
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
                    foreach ($productCats['category'] as $c) {
                        $productCategories[] = $c['slug'];
                    }

                    $productSubcategories = [];
                    foreach ($productCats['subcategory'] as $c) {
                        $productSubcategories[] = $c['slug'];
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
                        : $store->getBaseUrl() . $primaryProduct->getUrlKey()
                        . Mage::helper('catalog/product')->getProductUrlSuffix($parentStoreId);

                    // sum up merchandise total
                    $merchandiseSubtotal += (formatFloat($item->getPrice()) * formatFloat($item->getQtyOrdered(), 0));

                    // assign data to our variables
                    $productId[$x] = $primaryProduct->getId();
                    $productSimpleId[$x] = $item->getProductId();
                    $productSku[$x] = $item->getSku();
                    $productName[$x] = $item->getName();
                    $productOriginalPrice[$x] = formatFloat($item->getOriginalPrice());
                    $productPrice[$x] = formatFloat($item->getPrice());
                    $productQuantity[$x] = formatFloat($item->getQtyOrdered(), 0);
                    $productDiscountAmount[$x] = formatFloat($item->getDiscountAmount());
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
                    formatString($order->getCustomerEmail()), // customer_email, Required
                    $order->getOrderId(), // order_id, Required
                    $order->getCustomerId(), // customer_id, Required
                    formatString($order->getCustomerFirstName()), // customer_first_name, Required
                    formatString($order->getCustomerLastName()), // customer_last_name, Required
                    formatString($order->getCustomerStreetLine1()), // customer_street_line1
                    '', // customer_street_line2
                    formatString($order->getCustomerCity()), // customer_city, Required
                    formatString($order->getCustomerState()), // customer_state, Required
                    formatString($order->getCustomerZip()), // customer_zip, Required
                    formatString($order->getCustomerCountry()), // customer_country, Required
                    formatString($order->getCustomerCountry()), // country_code, Required
                    formatString($order->getShippingFirstName()), // customer_street_line1_shipping
                    formatString($order->getShippingLastName()), // customer_street_line2_shipping
                    formatString($order->getShippingStreetLine1()), // customer_street_line1_shipping
                    '', // customer_street_line2_shipping
                    formatString($order->getShippingCity()), // customer_city_shipping
                    formatString($order->getShippingState()), // customer_state_shipping
                    formatString($order->getShippingZip()), // customer_zip_shipping
                    formatString($order->getShippingCountry()), // customer_country_shipping
                    ORDER_TYPE, // order_type, Required
                    $order->getCreatedAt(), // order_created_date
                    $order->getStoreId(), // order_store, Required
                    $paymentType, // order_payment_type, Required
                    $shippingType, // order_shipping_type, Required
                    $order->getOrderPromoCode(), // order_promo_code, Required
                    $order->getOrderCurrencyCode(), // order_currency_code, Required
                    formatFloat($merchandiseSubtotal), // order_merchandise_total, Required
                    formatFloat($order->getOrderSubtotal()), // order_subtotal, Required
                    formatFloat($order->getOrderShipping()), // order_shipping_amount, Required
                    formatFloat($order->getOrderTax()), // order_tax_amount, Required
                    formatFloat($order->getOrderDiscountAmount()), // order_discount_amount, Required
                    formatFloat($storeCredit), // store_credit_payment_amt, Suggestion
                    formatFloat($order->getOrderGrandTotal()), // order_total, Required
                    formatArray($productBrand), // product_brand, Required
                    formatArray($productCategory, 'string', true, true), // product_category, Required
                    formatArray($productDiscountAmount, 'float'), // product_discount_amount, Suggested
                    formatArray($productId, 'int'), // product_id, Required
                    formatArray($productImageUrl), // product_image_url, Required
                    formatArray($productName), // product_name, Required
                    formatArray($productOriginalPrice, 'float'), // product_original_price, Required
                    formatArray($productPrice, 'float'), // product_price, Required
                    formatArray($productPromoCode), // product_promo_code, Required
                    formatArray($productQuantity, 'int'), // product_quantity, Required
                    formatArray($productSku), // product_sku, Required
                    formatArray($productSubcategory, 'string', true, true), // product_subcategory, Required
                    formatArray($productUrl), // product_url, Suggested
                    formatArray($siteName, 'string', true, true), // site_name, Required
                    formatArray($productSimpleId, 'int'), // product_simple_id, Suggested
                ];

                $eventsProcessed[] = $order->getEventId();
            } else {
                $eventsSkipped[] = $order->getEventId();
            }

            if (count($data) >= MAX_ROWS_PER_FILE) {
                // create file
                createFile($filename, $data, $stagingDirectory, $queueDirectory);

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
        createFile($filename, $data, $stagingDirectory, $queueDirectory);
    }

    // update our event records
    // pushed_to_tealium field / 1 = sent / 2 = skipped or not set
    foreach ($eventsProcessed as $event) {
        updateEvent($event, 1);
    }
    foreach ($eventsSkipped as $event) {
        updateEvent($event, 2);
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
    Mage::logException($e);
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
    $event = Mage::getModel('forevercompanies_tealium/event')
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
    $csv = new Varien_File_Csv();
    $csv->setEnclosure('"');
    $csv->saveData($stagingDirectory . DS . $filename, $data);

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
        $array = array_map('makeLowerCase', $array);
    }
    if ($removeSpaces) {
        $array = array_map('removeSpacesFromString', $array);
    }

    switch ($dataType) {
        case 'int':
        case 'float':
            return '[' . implode(', ', $array) . ']';
            break;

        case 'string':
        default:
            // surround string pieces with double quotes, required for csv output
            return formatString('["' . implode('", "', $array) . '"]');
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
    $collection = Mage::getModel('catalog/category')->getCollection()->setStoreId($storeId)
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('is_active', 1);

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
            'slug' => formatSlug($c->getName()),
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
