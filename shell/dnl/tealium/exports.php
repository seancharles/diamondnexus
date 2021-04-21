<?php
/**
 *
 *
 *
 */

/* ------------------------------------------

    Init

--------------------------------------------- */

    ini_set('display_errors', '1');
    require_once $_SERVER['HOME'].'/html/app/Mage.php';
    Mage::app();

    date_default_timezone_set("America/Chicago");


/* ------------------------------------------

    Functions

--------------------------------------------- */

    function dbReadViaSql($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $mdb->fetchAll($q);
    }
    function dbWriteViaSql($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $mdb->query($q);
    }

    function getTealConfig() {

        $tealData = null;
        $tealData['stores'][1] = [
                'name'=>'Diamond Nexus'
        ];
        return $tealData;

    }


    function setFcGlobals($outputAry) {
        $fcGlobals = [
            'site_name'=>["diamondnexus","www"]
            ,'page_name'=> Mage::app()->getLayout()->getBlock('head')->getTitle()
        ];
        return array_merge($fcGlobals,$outputAry);
    }
    function getFcBodyClasses() {
        $pgBodyClassSrc =  trim(Mage::app()->getLayout()->getBlock('root')->getBodyClass());
        $pgBodyClassAry =  explode(' ', $pgBodyClassSrc);
        return $pgBodyClassAry;
    }
    function fcGetUrlPath ($url = '') {
        if (empty($url)) {
            $url = Mage::helper('core/url')->getCurrentUrl();
        }
        $parsed = Mage::getSingleton('core/url')->parseUrl($url);
        return explode('/', $parsed->getPath());
    }
    function fcTealFormatStr($str,$subSpaces = true) {
        $output = trim(strtolower($str));
        if ($subSpaces) $output = str_replace(' ', '_', $output);
        return $output;
    }
    function fcTealFormatSlug($str) {
        $output = trim(strtolower($str));
        $output = str_replace(' ', '_', $output);
        $output = preg_replace("/[^a-z0-9_]+/i", "", $output);
        return $output;
    }
    function fcTealFormatAry($ary) {
        array_map('fcTealFormatStr',$ary);
        return '["'.implode('","', $ary).'"]';
    }
    function fcTealFormatPrice($amt) {
        return number_format((float)$amt, 2, '.', '');
    }
    function fcTealFormatUtagData($utagDataSrc) {
        $utagData = '';
        foreach ($utagDataSrc as $k=>$v) {
            if (is_array($v)) {
                $utagData .= ",\n\"" . $k . '": '. $this->fcTealFormatAry($v);
            } else {
                $utagData .= ",\n\"" . $k . '": "' . $v . '"';
            }
        }
        return $utagData;
    }
    function getFcConfigurableProduct($simpleProductId) {
        $output = '';
        return $output;
    }
    function fcTealGetProductCats($product) {
        $catSrc = $this->fcGetCategories();
        $output = '';
        foreach ($product->getCategoryIds() as $cid) {
            if (isset($catSrc[$cid])) {
                $levelType = ($catSrc[$cid]['navlevel'] == 2) ? 'category' : 'subcategory';
                $output[$levelType][] = $catSrc[$cid];
            }
        }
        return $output;
    }
    function fcGetCategoryById($catId) {
       return Mage::getModel('catalog/category')->load($catId);
    }
    function fcGetProduct($productId = '') {
        if (!empty($productId)) {
            $product = Mage::getModel('catalog/product')->load($productId);
        } else {
            $product = Mage::registry('current_product');
        }
        return $product;
    }
    function fcGetProductFilters() {
        $filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        return $filters;
    }
    function fcGetProductImage($product,$bundle = '') {

        return Mage::helper('catalog/image')->init($product, 'small_image')->resize(600, 600);

    }
    function fcGetCategories() {

        $dnCatsSrc = Mage::getModel('catalog/category')->getCollection()->setStoreId(1)
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
            if (in_array($c->getId(),[560,40])) $navLevel--;
            if (in_array($c->getId(),[425,860])) $navLevel++;
            $dnCats[$c->getId()] = ['id'=>$c->getId(),'name'=>$c->getName(),'slug'=>fcTealFormatSlug($c->getName()),'level'=>$c->getLevel(),'navlevel'=>$navLevel];
        }
        return $dnCats;
    }
    function fcGetQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote(); //Mage::helper('checkout/cart')->getCart()->getQuote();
        return $quote;

    }

    function tealFormatDate($ary) {
        array_map('fcTealFormatStr',$ary);
        return '["'.implode('","', $ary).'"]';
    }
    function tealFormatPrice($amt) {
        return number_format((float)$amt, 2, '.', '');
    }

    function getUrlCommand() {

        $msg = 'Ready.';
        $cmd = $_REQUEST['cmd'];
        switch ($cmd) {

            case 'sample_insert': {
                $msg = 'Sample data requested ' . date('r');
                getSampleAccounts();
                break;
            }
            case 'sample_truncate': {
                $msg = 'Truncate of Sample data requested ' . date('r');
                dbWriteViaSql('TRUNCATE TABLE `teal_accts`');
                break;
            }
            case 'sample_orders': {
                $msg = 'Sample orders requested ' . date('r');
                $start = $_REQUEST['start'];
                $end = $_REQUEST['end'];
                getSampleOrders($start,$end);
                break;
            }
            case 'get_orders': {
                $msg = 'Orders requested ' . date('r');
                $storeId = (!empty($_REQUEST['storeId'])) ? (int) $_REQUEST['storeId'] : 1;
                $startId = (int) $_REQUEST['startId'];
                $endId = (int) $_REQUEST['endId'];
                $startDate = $_REQUEST['startDate'];
                $endDate = $_REQUEST['endDate'];
                getOrders($storeId,$startId,$endId);
                break;
            }
            case 'get_cats': {
                getCatsByProductId(69271);
                break;
            }
            default:
                $msg = 'Command "'.$cmd.'" unknown. No action taken.';

        }

        echo $msg;
        exit;

    }
    function fcGetProductConfigurableId($simpleProductId) {
       $configurableProduct = Mage::getResourceSingleton('catalog/product_type_configurable')
          ->getParentIdsByChild($simpleProductId);
       return $configurableProduct;
    }

    function getOrdersByEmail($email) {

        $output = null;
        $sql = "SELECT
            entity_id
        FROM
            sales_flat_order
        WHERE
            customer_email = '".$email."'
        AND
            store_id = 1
        ORDER BY
            entity_id ASC
        ";
        $results = dbReadViaSql($sql);
        return $results;

    }
    function getOrdersDetails($orderIdsAry) {

        $output = null;
        $ordersTotalQty = 0;
        $ordersTotalPaid = 0;
        $ordersLast = null;

        foreach ($orderIdsAry as $o) {

            $order = Mage::getModel('sales/order')->load($o);

            $products = "SELECT
            order_id
            ,store_id
            ,product_id
            ,product_type
            ,sku
            ,name
            FROM
                sales_flat_order_item
            WHERE
                order_id IN (" . implode(',', $o) . ")   
            ";
            $results = dbWriteViaSql($products);

            foreach ($results as $i) {

                $output['products']['id'][] = $i['product_id'];
                $output['products']['sku'][] = $i['sku'];
                $output['products']['name'][] = $i['name'];

            }

            $ordersTotalQty += 1;
            $ordersTotalPaid += $order->getGrandTotal();
            if ($order->getCreatedAt() > $ordersLast) $ordersLast = $order->getCreatedAt();


        }

        $output['products']['id'] = tealFormatAry($output['products']['id']);
        $output['products']['sku'] = tealFormatAry($output['products']['sku']);
        $output['products']['name'] = tealFormatAry($output['products']['name']);

        $output['orders']['qty'] = $ordersTotalQty;
        $output['orders']['paid'] = $ordersTotalPaid;
        $output['orders']['last'] = $ordersLast;

        return $output;

    }



/* ------------------------------------------

    Export data

--------------------------------------------- */


// Get sample export of accounts with orders

function getSampleAccounts()
{

    $output = '';
    $sql = "INSERT INTO teal_accts 
    (
        id
        ,email
        ,magid
        ,name_first
        ,name_last
        ,name_middle
        ,city
        ,state
        ,postalcode
        ,countrycode
        ,orders_lifetime_qty
        ,orders_lifetime_spent
        ,orders_lifetime_returns
        ,orders_last
        ,orders_products_id
        ,orders_products_sku
        ,orders_products_name
        ,rewards_pending
        ,rewards_available
        ,rewards_spent
        ,gender
        ,birthdate
        ,anniversary
        ,lead_utms
        ,support_active
        ,mailinglist_active
        ,household
        ,status
        ,lastactivity
        ,updated_at
    )
    SELECT
        null
        ,sfo.customer_email
        ,sfo.customer_id as magid
        ,sfo.customer_firstname
        ,sfo.customer_lastname
        ,sfo.customer_middlename
        ,sfoa.city
        ,sfoa.region
        ,sfoa.postcode
        ,sfoa.country_id
        ,0
        ,0
        ,0
        ,sfo.created_at
        ,''
        ,''
        ,''
        ,0
        ,0
        ,0
        ,''
        ,sfo.customer_dob
        ,''
        ,''
        ,0
        ,0
        ,''
        ,''
        ,sfo.updated_at
        ,null
    FROM
        sales_flat_order sfo
    JOIN
        sales_flat_order_address sfoa ON (sfo.billing_address_id = sfoa.entity_id)
    WHERE
        store_id = 1
    AND
        status IN ('delivered')
    AND
        sfo.customer_id <> ''
    ORDER BY
        sfo.entity_id DESC
    LIMIT
        0,100 
    ";

    $insert = dbWriteViaSql($sql);

    $tealAccts = dbReadViaSql('select * from teal_accts order by id asc');

    foreach ($tealAccts as $a) {

        $acctOrderIds = getOrdersByEmail($a['email']);
        $acctOrderDetails = getOrdersDetails($acctOrderIds);

        $update = "UPDATE teal_accts
        SET
            orders_last = '" . $acctOrderDetails['orders']['last'] . "',
            orders_lifetime_qty = '" . $acctOrderDetails['orders']['qty'] . "',
            orders_lifetime_spent = '" . tealFormatPrice($acctOrderDetails['orders']['paid']) . "'
            ,orders_products_id = '" . $acctOrderDetails['products']['id'] . "'
            ,orders_products_sku = '" . $acctOrderDetails['products']['sku'] . "'
            ,orders_products_name = '" . $acctOrderDetails['products']['name'] . "'
        WHERE
            email = '" . $a['email'] . "'
        ";
        dbWriteViaSql($update);

    }


//    foreach ($tealAccts as $a) {
//
//        $customer = Mage::getSingleton('customer/session')->getCustomer();
//
//
//        $update = "UPDATE teal_accts
//        SET
//            orders_last = '".$acctOrderDetails['orders']['last']."',
//            orders_lifetime_qty = '".$acctOrderDetails['orders']['qty']."',
//            orders_lifetime_spent = '".tealFormatPrice($acctOrderDetails['orders']['paid'])."'
//            ,orders_products_id = '".$acctOrderDetails['products']['id']."'
//            ,orders_products_sku = '".$acctOrderDetails['products']['sku']."'
//            ,orders_products_name = '".$acctOrderDetails['products']['name']."'
//        WHERE
//            email = '".$a['email']."'
//        ";
//        dbWriteViaSql($update);
//
//
//    }


    return true;

}


function getSampleOrders ($startDate,$endDate = '',$statusAry = ['delivered']) {

    $output = null;

    // Insert orders
    $sql = "INSERT INTO teal_orders
        (id 
        ,email
        ,magid
        ,name_first
        ,name_last
        ,name_middle
        ,order_created
        ,order_status
        ,order_id
        ,order_increment_id
        ,order_total
        ,order_qty
        ,site_name
        )
    SELECT
        null
        ,sfo.customer_email
        ,sfo.customer_id as magid
        ,sfo.customer_firstname
        ,sfo.customer_lastname
        ,sfo.customer_middlename
        ,sfo.created_at
        ,sfo.status
        ,sfo.entity_id
        ,sfo.increment_id 
        ,sfo.grand_total
        ,sfo.total_qty_ordered
        ,'[\"diamondnexus\",\"www\"]'
    FROM
        sales_flat_order sfo
    WHERE
        sfo.status IN ('".implode("','",$statusAry)."') ";
    if (!empty($startDate) || !empty($endDate)) {
        $sql .= "AND sfo.created_at BETWEEN '" . $startDate . "' AND  '" . $endDate . "' ";
    }
    $sql .= "AND
        store_id = 1
    ORDER BY
        entity_id DESC
    ";

    //echo $sql;
    //exit;

    $orders = dbWriteViaSql($sql);


    // Insert orders products

    $sqlProducts = "Select DISTINCT order_id from teal_orders ORDER BY order_id DESC";
    $tealOrders = dbReadViaSql($sqlProducts);
    foreach ($tealOrders as $o) {

        $orderIdsAry = null;
        $orderIdsAry[] = $o['order_id'];

        //print_r($orderIdsAry);


        $products = "SELECT
            order_id
            ,store_id
            ,product_id
            ,product_type
            ,sku
            ,name
            FROM
                sales_flat_order_item
            WHERE
                order_id = ".$o['order_id']."
            ";
        $results = dbWriteViaSql($products);
        $productsInfo = null;


        foreach ($results as $p) {

            // Replace product_id with configurable ids if available
            $productConfigurableId = fcGetProductConfigurableId($p['product_id']);
            $productsInfo['products']['new_id'][] = (!empty($productConfigurableId)) ? $productConfigurableId[0] : $p['product_id'];
            $productsInfo['products']['simple_id'][] = $p['product_id'];
            $productsInfo['products']['id'][] = $p['product_id'];
            $productsInfo['products']['sku'][] = $p['sku'];
            $productsInfo['products']['name'][] = $p['name'];

        }
        // If configurables were found -- overwrite existing vals
        $productsInfo['products']['id'] = array_replace($productsInfo['products']['id'],$productsInfo['products']['new_id']);

        //echo json_encode($productsInfo['products']['id']) . "<br>\n";
        //echo json_encode($productsInfo['products']['id']) . ' ' . json_encode($productsInfo['products']['new_id']) . "<br>\n";

        $upd = "UPDATE teal_orders
        SET 
            order_product_id = '" . json_encode($productsInfo['products']['id']) . "'
            ,order_product_simple_id = '" . json_encode($productsInfo['products']['simple_id']) . "'
            ,order_product_sku = '" . json_encode($productsInfo['products']['sku']) . "'
            ,order_product_name = '" . json_encode($productsInfo['products']['name']) . "'
        WHERE order_id = " . $o['order_id'];

        dbWriteViaSql($upd);

    }

/*
    $sql = "UPDATE teal_orders
        SET
        
        
        
    INNER JOIN (SELECT
        
    ) as sfoi
        (null,
        ,sfo.customer_email
        ,sfo.customer_id as magid
        ,sfo.customer_firstname
        ,sfo.customer_lastname
        ,sfo.customer_middlename
        ,sfo.created_at
        ,sfo.status
        ,sfo.entity_id
        ,sfo.increment_id 
        ,sfo.grand_total
        ,sfo.qty
        )
    FROM
        sales_flat_order_items sfo
    JOIN
        sales_flat_order_items sfoi ON (sfo.entity_id = sfoi.order_id)
    WHERE
        status IN (".implode(',',$status).")
    AND
        store_id = 1
    ORDER BY
        entity_id DESC
    ";
    $products = dbWriteViaSql($sql);
    */

    return true;


}

function getCatsByProductId($pid) {

    global $fcCats;
    $product = Mage::getModel('catalog/product');
    $product->setId($pid);
    $categoryIds = $product->getResource()->getCategoryIds($product);

    echo json_encode($categoryIds);
    exit;

    return $categoryIds;

}

function getCategoryIdsByPid($productId) {

    $product = Mage::getModel('catalog/product');
    $product->setId($productId);
    $categoryIds = $product->getResource()->getCategoryIds($product);
    return $categoryIds;

}

function getOrdersItems($orderId) {

    $orderItems = [];

    $products = "SELECT
            order_id
            ,store_id
            ,product_id
            ,product_type
            ,sku
            ,name
            ,original_price
            ,price
            ,discount_amount
            ,'' as product_url
            ,qty_ordered
            ,'' as product_promo_code
            
            FROM
                sales_flat_order_item
            WHERE
                order_id = " . $orderId . "
            ";
    $results = dbWriteViaSql($products);


}

function getOrders($storeId = 1, $startId = '', $endId = '', $startDate = '',$endDate = '') {

    $output = null;

    $t = getTealConfig();

    // Insert orders
    $sql = "INSERT INTO teal_orders_new
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
        )
    SELECT
        null
,sfo.customer_id as magid
,sfo.customer_id as customer_id
,sfo.customer_email
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
,sfo.discount_amount as order_discount_amount
,'' as order_merchandise_total
,'' as order_payment_type
,sfo.shipping_amount as order_shipping_amount
,sfo.shipping_description as order_shipping_type
,".$storeId." as order_store
,'' as order_type
,sfo.subtotal as order_subtotal
,sfo.grand_total as order_total
,sfo.tax_amount as order_tax_amount
,'' as page_name
,'".$t['stores'][1]['name']."' as product_brand
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
,'[\"diamondnexus\",\"www\"]' as site_name
,'checkout' as site_section
,'order' as tealium_event
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
    else if (!empty($startDate) || !empty($endDate)) {
        $sql .= "AND sfo.created_at BETWEEN '" . $startDate . "' AND  '" . $endDate . "' ";
    }
    $sql .= "
    ORDER BY
        sfo.entity_id ASC
    ";


    $orders = dbWriteViaSql($sql);


    /*

    $sqlProducts = "Select DISTINCT order_id from teal_orders_new ORDER BY order_id DESC";
    $tealOrders = dbReadViaSql($sqlProducts);
    foreach ($tealOrders as $o) {

        $orderIdsAry = null;
        $orderIdsAry[] = $o['order_id'];


        // ,'' as product_category
        // ,'' as product_subcategory

        $products = "SELECT
            order_id
            ,store_id
            ,product_id
            ,product_type
            ,sku
            ,name
            ,original_price
            ,price
            ,discount_amount
            ,'' as product_url
            ,qty_ordered
            ,'' as product_promo_code
            
            FROM
                sales_flat_order_item
            WHERE
                order_id = " . $o['order_id'] . "
            ";
        $results = dbWriteViaSql($products);
        $productsInfo = null;

        foreach ($results as $p) {

            // Replace product_id with configurable ids if available
            $productConfigurableId = fcGetProductConfigurableId($p['product_id']);
            $productsInfo['products']['new_id'][] = (!empty($productConfigurableId)) ? $productConfigurableId[0] : $p['product_id'];
            $productsInfo['products']['simple_id'][] = $p['product_id'];
            $productsInfo['products']['id'][] = $p['product_id'];
            $productsInfo['products']['sku'][] = $p['sku'];
            $productsInfo['products']['name'][] = $p['name'];
            $productsInfo['products']['product_original_price'][] = $p['original_price'];
            $productsInfo['products']['product_price'][] = $p['price'];
            $productsInfo['products']['product_discount_amount'][] = $p['discount_amount'];
            $productsInfo['products']['product_quantity'][] = $p['qty_ordered'];
            $productsInfo['products']['product_promo_code'][] = "";
            $productsInfo['products']['product_url'][] = "";
            $productsInfo['products']['product_category'][] = "";
            $productsInfo['products']['product_subcategory'][] = "";

        }
        // If configurables were found -- overwrite existing vals
        $productsInfo['products']['id'] = array_replace($productsInfo['products']['id'], $productsInfo['products']['new_id']);

        //echo json_encode($productsInfo['products']['id']) . "<br>\n";
        //echo json_encode($productsInfo['products']['id']) . ' ' . json_encode($productsInfo['products']['new_id']) . "<br>\n";

        $upd = "UPDATE teal_orders_new
        SET 
            product_id = '" . json_encode($productsInfo['products']['id']) . "'
            ,product_simple_id = '" . json_encode($productsInfo['products']['simple_id']) . "'
            ,product_sku = '" . json_encode($productsInfo['products']['sku']) . "'
            ,product_name = '" . json_encode($productsInfo['products']['name']) . "'
            ,product_original_price = '" . json_encode($productsInfo['products']['product_original_price']) . "'
            ,product_price = '" . json_encode($productsInfo['products']['product_price']) . "'
            ,product_discount_amount = '" . json_encode($productsInfo['products']['product_discount_amount']) . "'
            ,product_quantity = '" . json_encode($productsInfo['products']['product_quantity']) . "'
            ,product_promo_code = '" . json_encode($productsInfo['products']['product_promo_code']) . "'
            ,product_url = '" . json_encode($productsInfo['products']['product_url']) . "'
            ,product_category = '" . json_encode($productsInfo['products']['product_category']) . "'
            ,product_subcategory = '" . json_encode($productsInfo['products']['product_subcategory']) . "'
        WHERE order_id = " . $o['order_id'];

        dbWriteViaSql($upd);



    }  */

    return true;

}

// Load up $cats
$fcCats = fcGetCategories();


// Do it
getUrlCommand();

