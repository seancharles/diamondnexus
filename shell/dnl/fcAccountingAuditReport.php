<?php
	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	
	Mage::app();
	
	ini_set('memory_limit', '4095M');

	$file = 'accounting-audit-report-' . time() . '.csv';
	$path = Mage::getBaseDir('var') . DS . 'export' . DS . $file;
	
	$dnStores = [5,7,8,18,1,9,10];
	$tfStores = [17,13,19,12];
	
	$fields = [
		'Order',
		'Email',
		'Total Paid',
		'Total Refunded',
		'Subtotal',
		'Shipping Amount',
		'Tax Amount',
		'Store Credit Amount',
		'Discount Amount',
		'Grand Total',
		'Status',
		'Order Date',
		'Ship Date',
		'Ship Name',
		'Ship Phone',
		'Bill Name',
		'Bill Phone',
		'Name Mismatch',
		'Phone Mismatch',
		'Amount Flag'
	];

	date_default_timezone_set('America/Chicago');
	
	$orderCollection = Mage::getModel('sales/order')->getCollection();
	
	$begin = date('Y-m-d', strtotime('01/01/2014')) . " 00:00:00";
	
	//$begin = date('Y-m-d', strtotime('midnight first day of last month')) . " 00:00:00";
	$end = date('Y-m-d', strtotime('midnight last day of last month')) . " 23:59:59";

	$orderCollection->addAttributeToFilter('created_at', array('gt' => $begin));
	$orderCollection->addAttributeToFilter('created_at', array('lt' => $end));
        $orderCollection->addFieldToFilter('status', array('in' => array('quote', 'pending','processing')));

	$fp = fopen($path, 'w+');

	fputcsv($fp, $fields);
	
	if( count($orderCollection) > 0 ) {
		
		foreach($orderCollection as $order) {
			
			$shipment = $order->getShipmentsCollection()->getFirstItem();
			//$shipmentDate = $shipment->getCreatedAt();
			
			$amountFlag = null;
			
			if( in_array($order->getStoreId(), $dnStores) == true) {
				if( $order->getGrandTotal() >= 5000 ) {
					$amountFlag = 'dn5000';
				}
			} else if( in_array($order->getStoreId(), $tfStores) == true) {
				if( $order->getGrandTotal() >= 10000 ) {
					$amountFlag = 'tf10000';
				}
			}
			
			$row = [
				$order->getIncrementId(),
				$order->getCustomerEmail(),
				$order->getTotalPaid(),
				$order->getTotalRefunded(),
				$order->getSubtotal(),
				$order->getShippingAmount(),
				$order->getTaxAmount(),
				$order->getCustomerBalanceAmount(),
				$order->getDiscountAmount(),
				$order->getGrandTotal(),
				$order->getStatus(),
				$order->getCreatedAt(),
				$order->getAnticipatedShipdate(),
				$order->getShippingAddress()->getName(),
				$order->getShippingAddress()->getTelephone(),
				$order->getBillingAddress()->getName(),
				$order->getBillingAddress()->getTelephone(),
				(($order->getShippingAddress()->getName() != $order->getBillingAddress()->getName()) ? '1' : '0'),
				(($order->getShippingAddress()->getTelephone() != $order->getBillingAddress()->getTelephone()) ? '1' : '0'),
				$amountFlag
			];
			
			fputcsv($fp, $row);
			
		}
	}
	
	$body = "<h2>Accounting Audit Report</h2>" .
			"File is attached:" .
			"<br />" .
			"<br />" .
			"<span style='font-size:10px;'>" . 
				"Sent From <strong>@mag4:{$_SERVER['PWD']}/{$_SERVER['SCRIPT_FILENAME']}</strong>" .
			"</span>";

	fclose($fp);
	$mail = new Zend_Mail();
	$mail->setBodyHtml($body);
	$mail->setFrom('it@forevercompanies.com', 'Forever Companies Reports');
	$mail->addTo('pasekei@gmail.com', 'edie pasek');
	$mail->addTo('ken.licau@forevercompanies.com', 'Ken Licau');
	$mail->addTo('becky.sappington@forevercompanies.com', 'Becky Sappington');
	$mail->addTo('charles.wiese@forevercompanies.com', 'Charles Wiese');
	$mail->addTo('paul.baum@forevercompanies.com', 'Paul Baum');
	$mail->setSubject('Accounting Audit Report');

	$content = file_get_contents($path);
	$attachment = new Zend_Mime_Part($content);
	$attachment->type = 'application/vnd.ms-excel';
	$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
	$attachment->encoding = Zend_Mime::ENCODING_BASE64;
	$attachment->filename = $file;

	$mail->addAttachment($attachment);                 
	$mail->send(); 


	echo "Complete report file: " . $file . "\n";
?>
