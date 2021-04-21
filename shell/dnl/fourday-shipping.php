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
$date      = date('Y-m-01', strtotime('now -120 day'));  // now -1 day
$enddate      = date('Y-m-t', strtotime('now'));  // now -1 day
$fromDate = $date.' 00:00:00';
$toDate = $enddate.' 23:59:59';

$filename = $_SERVER['HOME'].'/html/var/report/ship_orders_' . $date . '.csv';

$order_collection = Mage::getModel('sales/order')->getCollection()
	->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
	->addFieldToFilter('status', array('in' => array('Shipped')));

$report[0] = array("Order Id", "Order Status", "Email", "Shipped Date");

foreach ($order_collection as $order) {
	$sales_person = Mage::getModel('admin/user')->load($order->sales_person_id)->username;
	if (empty($sales_person)) {
		$sales_person = 'Web';
	}
	$ship_date = Mage::getResourceModel('sales/order_status_history_collection')
				->addAttributeToSelect('created_at')
			        ->addAttributeToFilter('status', array('eq'=>'Shipped'))
			        ->addAttributeToFilter('parent_id', array('eq' => $order->getId()))->load();
        foreach ($ship_date as $dd) {
                $shipped_date = $dd->created_at;
                break;
        }
	if ((strtotime($shipped_date) < strtotime('now -4 day')) && (strtotime($shipped_date) > strtotime('now -6 day'))) {
		#print $order->increment_id." ".$order->status." ".$order->customer_email." ".$shipped_date."\n";
		$report[] = array($order->increment_id, $order->status, $order->customer_email, $shipped_date);
	}

}
$csv=new Varien_File_Csv();
$csv->saveData($filename,$report);

$mail = new Zend_Mail();
$mail->setBodyHtml("Four to Five Ship Orders Report - " . $date. " \r\n")
->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
->addTo('jessica.nelson@diamondnexus.com')
->setSubject('Four to Five Ship Orders Report - ' . $date);

$content = file_get_contents($filename);
$attachment = new Zend_Mime_Part($content);
$attachment->type = mime_content_type($filename);
$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$attachment->encoding = Zend_Mime::ENCODING_BASE64;
$attachment->filename = 'ship_orders_' . $date . '.csv';

$mail->addAttachment($attachment);

$mail->send();
?>
