<?php

//    require_once $_SERVER['HOME'].'magento//Mage.php';
    Mage::app();

	/**
	 * Get the resource model
	 */
	$resource = Mage::getSingleton('core/resource');
	
	/**
	 * Retrieve the read connection
	 */
	$readConnection = $resource->getConnection('core_read');
    
	/**
	 * Store Model
	 */
	$storeModel = Mage::getSingleton('core/store');
	
    $websiteModel = Mage::getSingleton('core/website');
    
    // custom date
    if ( isset($argv)  && count($argv) == 2 ) {
    
        // example: php fcProgressiveLeasingDelivery.php yesterday
        // example: php fcProgressiveLeasingDelivery.php "jan 1st" "last friday"
        $date = date('Y-m-d', strtotime($argv[1]));
        
        print_r($argv);
    
    } else {
        
        $date = date('Y-m-d', strtotime('yesterday'));
    }
    
	$storeNameMap = array(
		
	);
	
	$bodyHTML = null;
	
    $fromDate = $date.' 00:00:00';
    $toDate = $date.' 23:59:59';

    $filename = $_SERVER['HOME'].'/html/var/report/progressive-leasing-delivery-' . $date . '.csv';

    $orderQuery = "SELECT
						o.store_id,
						o.increment_id,
                        '' lease_id,
						CONCAT(o.customer_firstname, ' ', o.customer_lastname) name,
						o.grand_total,
						o.status,
						MIN(h.created_at) delivery_date,
						o.created_at
					FROM
						sales_flat_order o
					INNER JOIN
						sales_flat_order_payment p ON o.entity_id = p.parent_id
					INNER JOIN
						sales_flat_order_status_history h ON o.entity_id = h.parent_id
					WHERE
						p.multipay_payment_method = 7
					AND
						o.created_at > CURRENT_DATE - INTERVAL 180 DAY
                    AND
                        h.status IN('delivered')
                    AND
                        o.status IN('delivered')
					GROUP BY
						o.entity_id
					HAVING
                        delivery_date BETWEEN '" . $fromDate . "' AND '" . $toDate . "';";
    
    echo $orderQuery . "\n";
    
    $orderResult = $readConnection->fetchAll($orderQuery);

    echo count($orderResult) . " rows found!\n";
    
    $report[0] = array(
		"Store Name",
        "Lease ID",
        "Client Name",
        "Invoice Amount",
        "Delivery Date"
    );
    
	$bodyHTML .= '<table border="1">';
	$bodyHTML .= '<tr>';
	$bodyHTML .= '<th>Store Name</th>';
	$bodyHTML .= '<th>Lease ID </th>';
	$bodyHTML .= '<th>Client Name</th>';
	$bodyHTML .= '<th>Invoice Amount</th>';
	$bodyHTML .= '<th>Delivery Date</th>';
	$bodyHTML .= '<th>Order ID</th>';
	$bodyHTML .= '</tr>';
	
    foreach($orderResult as $order) {
        
        $storeName = null;
        
        if( $order['store_id'] > 0 ) {
            $websiteId = $storeModel->load($order['store_id'])->getWebsiteId();
            
            if( $websiteId > 0 ) {
                $storeName = $websiteModel->load($websiteId)->getName();
            }
        }
        
		$temp = array(
            'store_name' => $storeName,
            'lease_id' => $order['lease_id'],
            'client_name' => $order['name'],
            'invoice_amount' => $order['grand_total'],
            'delivery_date' => date("m/d/y", strtotime($order['delivery_date']))
        );
		
        $report[] = $temp;
		
		$bodyHTML .= '<tr>';
		$bodyHTML .= '<td>'.$temp['store_name'].'</td>';
		$bodyHTML .= '<td>'.$temp['lease_id'].'</td>';
		$bodyHTML .= '<td>'.$temp['client_name'].'</td>';
		$bodyHTML .= '<td>'.$temp['invoice_amount'].'</td>';
        $bodyHTML .= '<td>'.$temp['delivery_date'].'</td>';
		$bodyHTML .= '<td>'.$order['increment_id'].'</td>';
		$bodyHTML .= '</tr>';
		
    }
	
	$bodyHTML .= '</table>';
    
    $csv=new Varien_File_Csv();
    $csv->saveData($filename,$report);

    $mail = new Zend_Mail();
    $mail->setBodyHtml(
            "Daily Progressive Leasing Delivery Report - " . $date .
            "<br />" . 
            "<br />" . 
			$bodyHTML .
			"<br />" .
            "Forward spreadsheet with Lease Id column populated to readytofund@progleasing.com" .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" . 
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" . 
            "</span>"
        )
        ->setFrom('reports@forevercompanies.com', 'Forever Companies Reports')
        ->setReplyTo('no-reply@forevercompanies.com', 'No Reply')
        ->addTo('paul.baum@forevercompanies.com')
		->addTo('accounting@forevercompanies.com')
        ->addTo('jessica.nelson@diamondnexus.com')
        //->addTo('readytofund@progleasing.com')
        ->setSubject('Daily Progressive Leasing Delivery Report - ' . $date . ( (count($orderResult) == 0) ? ': No Orders Found' : '' ));

    $content = file_get_contents($filename);
    $attachment = new Zend_Mime_Part($content);
    $attachment->type = mime_content_type($filename);
    $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
    $attachment->encoding = Zend_Mime::ENCODING_BASE64;
    $attachment->filename = 'progressive-leasing-delivery-' . $date . '.csv';

    if( count($orderResult) > 0 ) {
        $mail->addAttachment($attachment);
    }
    
    $mail->send();
    
    echo "Email sent!\n";
?>
