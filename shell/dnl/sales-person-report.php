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
$date      = date('Y-m-d', strtotime('now -1 day'));  // now -1 day
$fromDate = $date.' 06:00:00';
$tosdate      = date('Y-m-d', strtotime('now'));  // now -1 day
$toDate = $tosdate.' 06:00:00';

$filename = $_SERVER['HOME'].'/html/var/report/sp_' . $date . '.csv';

$order_collection = Mage::getModel('sales/order')->getCollection()
	->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate));

$report[0] = array("Order Id", "Sales Person","Email");

foreach ($order_collection as $order) {
	$sales_person = Mage::getModel('admin/user')->load($order->sales_person_id)->username;
	if (empty($sales_person)) {
		$sales_person = 'Web';
	}
	$report[] = array($order->increment_id, $sales_person, $order->customer_email);
}
$csv=new Varien_File_Csv();
$csv->saveData($filename,$report);

$mail = new Zend_Mail();
$mail->setBodyHtml("All Sales Person Report - " . $date. " \r\n")
->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
->addTo('epasek@forevercompanies.com')
->addTo('bill.tait@forevercompanies.com')
->addTo('jessica.nelson@diamondnexus.com')
->addTo('ken.licau@forevercompanies.com')
->addTo('andrew.roberts@forevercompanies.com')
->addTo('mitch.stark@forevercompanies.com')
->setSubject('Sales Person Report - ' . $date);

$content = file_get_contents($filename);
$attachment = new Zend_Mime_Part($content);
$attachment->type = mime_content_type($filename);
$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$attachment->encoding = Zend_Mime::ENCODING_BASE64;
$attachment->filename = 'sp_' . $date . '.csv';

$mail->addAttachment($attachment);

$mail->send();
?>
