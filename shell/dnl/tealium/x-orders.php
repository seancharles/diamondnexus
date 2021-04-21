<?php
/**
 * Get globals
 */
set_time_limit(600);
require('globals.inc.php');

/**
 * Order exports
 */

function fcIsEngagementring($sku) {

    if (preg_match("/^LREN([A-Z0-9]{6})[^B](.*)/i", $sku)) {
        return true;
    }
    return false;

}
function fcIsWeddingband($sku) {

    if (preg_match("/(?:(^LREN([A-Z0-9]{6})[B](.*))|^LRWB)/i", $sku)) {
        return true;
    }
    return false;

}

function fcGetNavCatIds() {

    return [
        // Primary cats
        588,563,425,860,
        // Subcats
        40,145,560,564,834,837,566,567,568,813,844
    ];

}

// Get all categories info
function fcGetCats() {

    global $tealData;
    $dnCatsSrc = Mage::getModel('catalog/category')->getCollection()->setStoreId($tealData['brands']['dn']['store'])
  ->addAttributeToSelect('*')//or you can just add some attributes
  //->addAttributeToFilter('level', 2)//2 is actually the first level, default is 1
  //->addAttributeToFilter('store_id', 1)
  ->addAttributeToFilter('is_active', 1); //only active categories

    $dnCats = [];
    $dnCatsLevels = [];
    foreach ($dnCatsSrc as $c) {
        // Get 'navlevel' to reflect visual navigation to be able to separate cats and subcats for tealium
        // Notes: Upgrading Wedding bands & loose stones and degrading clearance & gifts to subcats
        $navLevel = $c->getLevel();
        $navItemPublic = (in_array($c->getId(),fcGetNavCatIds())) ? 1 : 0; // $c->getIncludeInMenu()
        if (in_array($c->getId(),[560,40])) $navLevel--;
        if (in_array($c->getId(),[425,860])) $navLevel++;
        $dnCats[$c->getId()] = ['id'=>$c->getId(),'name'=>$c->getName(),'slug'=>fcTealFormatSlug($c->getName()),'level'=>$c->getLevel(),'navlevel'=>$navLevel,'public'=>$navItemPublic];
    }
    return $dnCats;
}
function fcGetCategoryById($catId) {
   return Mage::getModel('catalog/category')->load($catId);
}
function fcGetProductCatIds($pid) {

    $product = Mage::getModel('catalog/product');
    $product->setId($pid);
    $categoryIds = $product->getResource()->getCategoryIds($product);
    return $categoryIds;

}
function fcGetProductCats($pid,$primaryCatsOnly = true) {

    global $fcCats;
    $catIds = fcGetProductCatIds($pid);
    if ($primaryCatsOnly) {
        $cat = '';
        $subcat = '';
        foreach ($catIds as $cid) {
            if ($fcCats[$cid]['navlevel'] == 2 && $fcCats[$cid]['public'] == 1 && $fcCats[$cid]['slug'] != '') $cat = $fcCats[$cid]['slug'];
        }
        foreach ($catIds as $cid) {
            if ($fcCats[$cid]['navlevel'] == 3 && $fcCats[$cid]['public'] == 1 && $fcCats[$cid]['slug'] != '') $subcat = $fcCats[$cid]['slug'];
        }
    } else {
        foreach ($catIds as $cid) {
            if ($fcCats[$cid]['navlevel'] == 2 && $fcCats[$cid]['slug'] != '') $cat[] = $fcCats[$cid]['slug'];
        }
        foreach ($catIds as $cid) {
            if ($fcCats[$cid]['navlevel'] == 3 && $fcCats[$cid]['slug'] != '') $subcat[] = $fcCats[$cid]['slug'];
        }

    }

    // Output
    return ['product_category' => $cat, 'product_subcategory' => $subcat];

}

function fcGetProductConfigurableId($simpleProductId) {
   $configurableProduct = Mage::getResourceSingleton('catalog/product_type_configurable')
      ->getParentIdsByChild($simpleProductId);
   if (is_array($configurableProduct)) return $configurableProduct[0];
   return $configurableProduct;
}
function fcGetProduct($pid = '') {
    if (!empty($pid)) {
        $product = Mage::getModel('catalog/product')->load($pid);
    }
    return $product;
}
function fcGetProductImage($product) {
    if (empty($product)) return '';
    return Mage::helper('catalog/image')->init($product, 'small_image')->resize(600, 600);

}
function fcGetProductUrl($product) {
    if (empty($product)) return '';
    return $product->getProductUrl();
}

function fcReplaceStr($col,$strOld,$strNew) {

    $sql = "UPDATE teal_orders_new_bak
SET product_image_url = REPLACE(product_image_url, 'joel.', 'content.') 
WHERE product_image_url LIKE '%joel.%';";

    $sql2 = "UPDATE teal_orders_new
SET product_url = REPLACE(product_url, 'joel.', 'www.') 
WHERE product_url LIKE '%joel.%';";

    return true;

}

function fcRemoveByEmailStr($emailStrAry = ['%diamondnexus%','%forevercompanies%']) {

    $sql = "select * from `teal_orders_new` where customer_email like '%forevercompanies%' OR customer_email like '%diamondnexus%';";
    foreach ($emailStrAry as $str) {
        $sql .= '';
    }

    return true;

}

function fcMigrateOrders($startId,$endId,$storeId = 1) {

    global $tealData;

    /* $orders = null;

    foreach ($orders as $o) {

        $items = getOrdersItems($o['order_id']);

        foreach ($items as $product) {

            //$cat =

        }

    } */

    $sql = "REPLACE INTO {$tealData['config']['orders']['table']} 
        (id
        ,magid
        ,customer_id
        ,customer_email
        ,customer_first_name
        ,customer_last_name
        ,customer_middle_name
        ,customer_city
        ,customer_state
        ,customer_country
        ,customer_postal_code
        ,order_created
        ,order_status
        ,order_currency_code
        ,order_id
        ,order_increment_id
        ,order_discount_amount
        ,order_merchandise_total
        ,order_payment_type
        ,order_shipping_amount
        ,order_shipping_type
        ,order_store
        ,order_type
        ,order_subtotal
        ,order_total
        ,order_tax_amount
        ,page_name
        ,product_brand
        ,product_category
        ,product_subcategory
        ,product_id
        ,product_simple_id
        ,product_sku
        ,product_image_url
        ,product_name
        ,product_original_price
        ,product_price
        ,product_discount_amount
        ,product_url
        ,product_quantity
        ,product_promo_code
        ,site_name
        ,site_section
        ,tealium_event
        ,engagement_ring_sku
        ,engagement_ring_name
        ,wedding_band_sku
        ,wedding_band_name
        )
    SELECT
        null
        ,sfo.customer_id as magid
        ,sfo.customer_id as customer_id
        ,LOWER(sfo.customer_email)
        ,sfo.customer_firstname as customer_first_name
        ,sfo.customer_lastname as customer_last_name
        ,sfo.customer_middlename as customer_middle_name
        ,sfoa.city as customer_city
        ,sfoa.region as customer_state
        ,sfoa.country_id as customer_country
        ,sfoa.postcode as customer_postal_code
        ,sfo.created_at as order_created
        ,sfo.status as order_status
        ,sfo.order_currency_code
        ,sfo.entity_id as order_id
        ,sfo.increment_id as order_increment_id
        ,ROUND(sfo.discount_amount,2) as order_discount_amount
        ,'' as order_merchandise_total
        ,'' as order_payment_type
        ,ROUND(sfo.shipping_amount,2) as order_shipping_amount
        ,sfo.shipping_description as order_shipping_type
        ,".$storeId." as order_store
        ,'' as order_type
        ,ROUND(sfo.subtotal,2) as order_subtotal
        ,ROUND(sfo.grand_total,2) as order_total
        ,ROUND(sfo.tax_amount,2) as order_tax_amount
        ,'' as page_name
        ,'".$tealData['brands']['dn']['name']."' as product_brand
        ,'' as product_category
        ,'' as product_subcategory
        ,'' as product_id
        ,'' as product_simple_id
        ,'' as product_sku
        ,'' as product_image_url
        ,'' as product_name
        ,'' as product_original_price
        ,'' as product_price
        ,'' as product_discount_amount
        ,'' as product_url
        ,'' as product_quantity
        ,'' as product_promo_code
        ,'".fcTealFormatAry($tealData['brands']['dn']['site'])."' as site_name
        ,'checkout' as site_section
        ,'order' as tealium_event
        ,''
        ,''
        ,''
        ,''
    FROM
        sales_flat_order sfo
    JOIN
	    sales_flat_order_address sfoa ON (sfo.billing_address_id = sfoa.entity_id)
    WHERE
        store_id  = $storeId
    ";
    if (!empty($startId) || !empty($endId)) {
        $sql .= "AND sfo.entity_id BETWEEN '" . $startId . "' AND  '" . $endId . "' ";
    }
//    else if (!empty($startDate) || !empty($endDate)) {
//        $sql .= "AND sfo.created_at BETWEEN '" . $startDate . "' AND  '" . $endDate . "' ";
//    }
    $sql .= "
    ORDER BY
        sfo.entity_id ASC
    ";
    $orders = dbWriteViaSql($sql);

    return true;


}
function fcMigrateOrdersItems($startId,$endId) {

    $sqlProducts = "Select DISTINCT order_id from sales_flat_order_item WHERE order_id BETWEEN " . $startId . " AND  " . $endId . " ORDER BY order_id ASC";
    $tealOrders = dbReadViaSql($sqlProducts);

    foreach ($tealOrders as $o) {

        if (!empty($o['order_id'])) {

            $products = "SELECT
            order_id
            ,store_id
            ,product_id
            ,product_type
            ,sku
            ,name
            ,ROUND(original_price,2) as original_price
            ,ROUND(price,2) as price
            ,ROUND(discount_amount,2) as discount_amount
            ,'' as product_url
            ,ROUND(qty_ordered,1) as qty_ordered
            ,'' as product_promo_code
            
            FROM
                sales_flat_order_item
            WHERE
                order_id = " . $o['order_id'] . "
            ";

            $results = dbWriteViaSql($products);
            $productsInfo = null;

            // Tealium wants notice if this product is a engagement ring or wedding band
            $lastRingUpdate = '';
            $lastBandUpdate = '';

            foreach ($results as $p) {

                // Replace product_id with configurable ids if available
                $productConfigurableId = fcGetProductConfigurableId($p['product_id']);
                $primaryProductId = (!empty($productConfigurableId)) ? $productConfigurableId : $p['product_id'];
                $productsInfo['products']['new_id'][] = $primaryProductId;

                $primaryProduct = fcGetProduct($primaryProductId);

                $productsInfo['products']['simple_id'][] = $p['product_id'];
                $productsInfo['products']['id'][] = $p['product_id'];
                $productsInfo['products']['sku'][] = $p['sku'];
                $productsInfo['products']['name'][] = addslashes($p['name']);
                $productsInfo['products']['product_original_price'][] = $p['original_price'];
                $productsInfo['products']['product_price'][] = $p['price'];
                $productsInfo['products']['product_discount_amount'][] = $p['discount_amount'];
                $productsInfo['products']['product_quantity'][] = $p['qty_ordered'];
                $productsInfo['products']['product_promo_code'][] = "";
                $productsInfo['products']['product_url'][] = fcGetProductUrl($primaryProduct);
                $productsInfo['products']['product_image_url'][] = fcGetProductImage($primaryProduct);
                $productsCats = fcGetProductCats($primaryProductId);
                $productsInfo['products']['product_category'][] = $productsCats['product_category'];
                $productsInfo['products']['product_subcategory'][] = $productsCats['product_subcategory'];


                // Tealium wants notice if this product is a engagement ring or wedding band
                if (fcIsEngagementring($p['sku'])) {
                    $lastRingUpdate = "\n" . ',engagement_ring_sku="' . $p['sku'] . '",engagement_ring_name="' . addslashes($p['name']) . '"';
                }
                if (fcIsWeddingband($p['sku'])) {
                    $lastBandUpdate = "\n" . ',wedding_band_sku="' . $p['sku'] . '",wedding_band_name="' . addslashes($p['name']) . '"';
                }

            }
            // If configurables were found -- overwrite existing vals
            if (!empty($productsInfo)) {

                $productsInfo['products']['id'] = array_replace($productsInfo['products']['id'], $productsInfo['products']['new_id']);

                $upd = "UPDATE teal_orders_new
        SET 
            product_id = '" . fcTealFormatNumAry($productsInfo['products']['id']) . "'
            ,product_simple_id = '" . fcTealFormatNumAry($productsInfo['products']['simple_id']) . "'
            ,product_sku = '" . fcTealFormatAry($productsInfo['products']['sku']) . "'
            ,product_name = '" . fcTealFormatAry($productsInfo['products']['name']) . "'
            ,product_original_price = '" . fcTealFormatNumAry($productsInfo['products']['product_original_price']) . "'
            ,product_price = '" . fcTealFormatNumAry($productsInfo['products']['product_price']) . "'
            ,product_discount_amount = '" . fcTealFormatNumAry($productsInfo['products']['product_discount_amount']) . "'
            ,product_quantity = '" . fcTealFormatNumAry($productsInfo['products']['product_quantity']) . "'
            ,product_promo_code = '" . fcTealFormatAry($productsInfo['products']['product_promo_code']) . "'
            ,product_url = '" . fcTealFormatAry($productsInfo['products']['product_url']) . "'
            ,product_image_url = '" . fcTealFormatAry($productsInfo['products']['product_image_url']) . "'
            ,product_category = '" . fcTealFormatAry($productsInfo['products']['product_category']) . "'
            ,product_subcategory = '" . fcTealFormatAry($productsInfo['products']['product_subcategory']) . "' ";
                if (!empty($lastRingUpdate)) $upd .= $lastRingUpdate;
                if (!empty($lastBandUpdate)) $upd .= $lastBandUpdate;
                $upd .= "WHERE order_id = " . $o['order_id'];

                //echo $upd;
                //exit;

                dbWriteViaSql($upd);

                //echo $upd;
                //exit;

            }

        }

    }

    return true;

}
function fcClearData() {

    global $tealData;
    return fcTealTruncate($tealData['config']['orders']['table']);

}

function exportOrders($startId,$endId) {

    $msg[] = 'Request received to export orders. ';
    // Insert orders
    $msg[] = fcMigrateOrders($startId,$endId);
    // Insert order items
    $msg[] = fcMigrateOrdersItems($startId,$endId);

    return json_encode($msg);
}

function fcRun($cmd = '')
{

    $msg = 'Ready.';
    $cmd = $_REQUEST['cmd'];
    switch ($cmd) {

        case 'product_cats':
            $msg = 'Order cat run ' . date('r');
            $msg = getProductCats(69271);
            break;
        case 'full_export':
            $msg = 'Order export requested ' . date('r') . "\n";
            $msg .= exportOrders(471380,471580);
            break;
        case 'orders_export':
            $msg = 'Order export requested ' . date('r') . "\n";
            $start = $_REQUEST['start']; // 471380
            $end = $_REQUEST['end']; // 471580
            $msg .= fcMigrateOrders($start,$end);
            break;
        case 'ordersdetails_export':
            $msg = 'Order details export requested ' . date('r') . "\n";
            $start = $_REQUEST['start']; // 471380
            $end = $_REQUEST['end']; // 471580
            $msg .= fcMigrateOrdersItems($start,$end);
            break;
        case 'sample_orders_export':
            $msg = 'Sample order export requested ' . date('r') . "\n";
            $msg .= exportOrders(471380,471880);
            break;
        case 'orders_clear':
            $msg = 'Clear order export requested ' . date('r') . "\n";
            $msg .= fcClearData();
            break;
        default:
            $msg = 'Command "' . $cmd . '" unknown. No action taken.';

    }
    echo '<pre>' . $msg;
    exit;

}



/**
 * Execute
 */

// Load up $cats
$fcCats = fcGetCats();

//print_r($fcCats);
//exit;

// Run it
fcRun();







