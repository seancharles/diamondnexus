<?php
	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	
	Mage::app();
	
	ini_set('memory_limit', '4095M');

	$file = 'accounting-open-order-report-' . time() . '.csv';
	$path = Mage::getBaseDir('var') . DS . 'export' . DS . $file;
	
	$dnStores = [5,7,8,18,1,9,10];
	$tfStores = [17,13,19,12];
	
	$fields = [
		'Order',
		'Email',
		'Total Paid',
		'Store Credit'
	];

	date_default_timezone_set('America/Chicago');
	
	$orderCollection = Mage::getModel('sales/order')->getCollection();
	
	$begin = date('Y-m-d', strtotime('01/01/2014')) . " 00:00:00";
	$end = date('Y-m-d', time()) . " 23:59:59";
	
    $orderCollection->getSelect()->where(
        "(created_at > '" . $begin . "' AND created_at < '" . $end . "')" .
		"AND " .
		"status IN('Processing','Pending','Quote','Pending Payment')" .
		"AND " .
		"(total_paid > 0 OR customer_balance_amount > 0)"
    );

	$fp = fopen($path, 'w+');

	fputcsv($fp, $fields);
	
	if( count($orderCollection) > 0 ) {
		
		foreach($orderCollection as $order) {
			$row = [
				$order->getIncrementId(),
				$order->getCustomerEmail(),
				$order->getTotalPaid(),
				$order->getCustomerBalanceAmount()
			];
			
			fputcsv($fp, $row);
			
		}
	}
	
	$body = "<h2>Accounting Open Order Report</h2>" .
			"File is attached:" .
			"<br />" .
			"<br />" .
			"<span style='font-size:10px;'>" . 
				"Sent From <strong>@mag4:{$_SERVER['PWD']}/{$_SERVER['SCRIPT_FILENAME']}</strong>" .
			"</span>";
	
	$mail = new Zend_Mail();
	$mail->setBodyHtml($body);
	$mail->setFrom('it@forevercompanies.com', 'Forever Companies Reports');
	$mail->addTo('ken.licau@forevercompanies.com', 'Ken Licau');
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

	fclose($fp);

	echo "Complete report file: " . $file . "\n";
?>
