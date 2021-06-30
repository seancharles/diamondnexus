<?php
/**
 *
 * Run at 2pm to update available by filter -- add/remove attribute from products
 *
 */

//    require_once $_SERVER['HOME'] . 'magento//Mage.php';

    Mage::app();


    function dbReadViaSql ($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $mdb->fetchAll($q);
    }
    function dbWriteViaSql ($q) {
        $mdb = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $mdb->query($q);
    }

    // Set Debug status to true to test; no db changes are made.
    $debug = false;


    date_default_timezone_set("America/Chicago");
    $orderDate = (!empty($startDate)) ? new DateTime($startDate) : new DateTime();
    $attrCode = 'filter_availableby';
    $daysToShip = 2; // Calc using ship speed of 1 or 2 days ?

    $attr = Mage::getModel('eav/config')->getAttribute('catalog_product', $attrCode);
    $attrOptions = $attr->getSource()->getAllOptions(true, true);

    $targetDate = '';
    $attrs = [];
    foreach ($attrOptions as $s) {

        // Manually hook up attribute code values to dates
	// Note: Only handle the filters we want to show up in the nav
        switch ($s['value']) {
			
            case 3260: // christmas
                $targetDate = date('Y') . '-12-25';
                break;
			
             case 3261: // new years eve
                $targetDate = date('Y') . '-12-31';
                break;
			
			case 3273: // valentines day
				$targetDate = date('Y') . '-02-14';
				break;
			
			
            default:
		$s['value'] = null;
        }

        // Get target's -- label, value, and date
        if (!empty($s['value'])) {
            $attrs[$s['value']] = array('label'=> $s['label'], 'value' => $s['value'], 'date' => $targetDate);
        }

    }


    //use this array to set start and end dates for filters
    //if not specified in this array filters will be added just as
    //included in the case statement above
    //fyi field is just to help identify which code you are working on
    $date_arr = array(
        3260 => array(
            'fyi' => 'christmas',
            'start_date'   => 20201204,
            'end_date' => 20201221
        ),
        3261 => array(
            'fyi'    => 'new years',
            'start_date' => 20201210,
            'end_date'   => 20201228
        ),
        3273 => array(
            'fyi'    => 'valentines day',
            'start_date' => 20200127,
            'end_date'   => 20200212
        )
    );


    $current_date = (int) date('Ymd');
    foreach( $date_arr as $code => $darr ){

        if( !empty( $attrs[$code] ) ){
            //check if today is before the start date
            if( $current_date < $darr['start_date'] ){
                $test1 = 'true';
            }else{
                $test1 = 'false';
            };
            //check if today is after the end date
            if( $current_date >= $darr['end_date'] ){
                $test2 = 'true';
            }else{
                $test2 = 'false';
            };

            //if it's before the start date or after the end date -- remove it
            //from the filters array set above
            if( $test1 == 'true' || $test2 == 'true' ){
                unset($attrs[$code]);
            }
        }
    }

    if ($debug) {
        echo 'Debug attrs: ' . json_encode($attrs);
        echo "\n";
    }


    $sql = 'select
	entity_id, type_id, sku, shipping_status, shipping_status_value, name, updated_at
from
	catalog_product_flat_1
where
    status = 1
order by
	entity_id';

    if ($debug) {
        $sql .= ' limit 0,500';
    }

    $products = dbReadViaSql($sql);

    $orderDateStr = date_format($orderDate,'Y-m-d H:i:s');

    $targetGroups = [];
    $productIdsAry = [];
    $update = [];
    $update['removeVal'] = null;


    // Loop over active attributes
    foreach ($attrs as $s) {

        $validShippingStatuses =  Mage::helper('shipdate')->getAvailableShippingStatuses($s['date'],$orderDateStr,$daysToShip);

        if ($debug) {
            echo 'Debug validShippingStatuses for '.$s['date'].': ' . json_encode($validShippingStatuses);
            echo "\n";
        }

        foreach ($products as $p) {

             if (in_array($p['shipping_status'], $validShippingStatuses)) {

                 //$productIdsAry[$s['value']][] = $p['entity_id'];
                 $productIdsAry[$p['entity_id']][] = $s['value'];

             }

        }


    }
    // Set a value for every product
    foreach ($products as $p) {

        if (!$productIdsAry[$p['entity_id']]) {
            $productIdsAry[$p['entity_id']] = $update['removeVal'];
        } else {
            $productIdsAry[$p['entity_id']] = implode(",", $productIdsAry[$p['entity_id']]);
        }

    }
    // Prepare vals for mass upsert
    $productIdGroups = [];
    foreach ($productIdsAry as $k => $v) {
        $productIdGroups[$v][] = $k;
    }

    if ($debug) {
        echo 'Debug productIdsAry: ' . json_encode($productIdsAry);
        echo "\n";
    }

    if ($debug) {
        echo 'Debug productIdGroups: ' . json_encode($productIdGroups);
        echo "\n";

        echo 'Debug completed:  ' . date('r');

        exit;

    }

    $helperProduct = Mage::helper('diamondnexus_product');
    $helperShip = Mage::helper('shipdate');
    $results = '';

    foreach ($productIdGroups as $k => $v) {

		if (empty($k)) $k = null;

		foreach($v as $key => $productId) {
			echo "ProductId = " . $productId . "\n";
			
			Mage::getSingleton('catalog/resource_product_action')->updateAttributes(
			   $productId,
			   array($attrCode => $k),
			   Mage_Core_Model_App::ADMIN_STORE_ID
			);
		}

        //$results = $helperProduct->upsertProductsAttribute($v, $attrCode, $k);
    }

    echo date('r') . ': Completed updating ' . count($productIdsAry) . ' products.';

	// dnShippingCutoffFlush.php
	// echo system('/usr/bin/php /home/admin/shell/dnl/redis-clear-product-page.php 10.62.37.134 6379 4');
	// echo system('/usr/bin/php /home/admin/limelight/dnFlushProductsByTag.php');
