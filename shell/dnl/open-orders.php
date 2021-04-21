<?php
//ini_set('display_errors', '1');
require_once $_SERVER['HOME'].'/html/app/Mage.php';
Mage::app();

// Get the current store id
$storeId = Mage::app()->getStore()->getId();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);

// Date
$date      = date('Y-m-01', strtotime('now -1 month'));  // now -1 day
$enddate      = date('Y-m-t', strtotime('now -1 month'));  // now -1 day
$fromDate = $date.' 00:00:00';
$toDate = $enddate.' 23:59:59';

$filename = $_SERVER['HOME'].'/html/var/report/open_orders_' . $date . '.csv';

$order_collection = Mage::getModel('sales/order')->getCollection()
	->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
	->addFieldToFilter('status', array('in' => array('Processing', 'Pending')));	

$report[0] = array("Order Id","Order Date", "Order Status", "Email", "Sub Total", "Total Due", "Sales Person", "Total Refunded","Shipping Amount","Discount Amount");

foreach ($order_collection as $order) {
	$sales_person = Mage::getModel('admin/user')->load($order->sales_person_id)->username;
	if (empty($sales_person)) {
		$sales_person = 'Web';
	}
	$report[] = array($order->increment_id, $order->created_at, $order->status, $order->customer_email, $order->subtotal, $order->total_due, $sales_person, $order->total_refunded, $order->shipping_amount, $order->discount_amount);
}

$csv=new Varien_File_Csv();
$csv->saveData($filename,$report);

$mail = new Zend_Mail();
$mail->setBodyHtml("All Open Orders Report - " . $date. " \r\n")
->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
->addTo('epasek@forevercompanies.com')
//->addTo('bill.tait@forevercompanies.com')
//->addTo('jessica.nelson@diamondnexus.com')
->addTo('ken.licau@forevercompanies.com')
//->addTo('andrew.roberts@forevercompanies.com')
->setSubject('Open Orders Report - ' . $date);

$content = file_get_contents($filename);
$attachment = new Zend_Mime_Part($content);
$attachment->type = mime_content_type($filename);
$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$attachment->encoding = Zend_Mime::ENCODING_BASE64;
$attachment->filename = 'open_orders_' . $date . '.csv';

$mail->addAttachment($attachment);

$mail->send();
?>
