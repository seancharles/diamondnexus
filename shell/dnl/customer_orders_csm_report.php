<?php
//    require_once $_SERVER['HOME'].'magento//Mage.php';
    Mage::app();
    $storeId = Mage::app()->getStore()->getId();
    $userModel = Mage::getModel('admin/user');
    $userModel->setUserId(0);
    Mage::getSingleton('admin/session')->setUser($userModel);

    $dates = array(30 => 90, 150 => 210, 335 => 395);
    foreach ($dates as $to => $from) {
	$date_start = date('Y-m-d', strtotime('-'.$to.' days'));
	$date_end = date('Y-m-d', strtotime('-'.$from.' days'));
	$orderItem = Mage::getModel('sales/order')->getCollection();
	$orderItem->addAttributeToFilter('created_at', array('from' => $date_end, 'to' => $date_start));
	$email_list = array();
	foreach ($orderItem as $item) {
	    if (empty($email_list[$item->getCustomerEmail()])) {
		$email_list[$item->getCustomerEmail()] = array($item->getIncrementId(), $item->getCreatedAt());
	    }
	} 
	$output = fopen($_SERVER['HOME'].'/html/var/export/csm-list-'.$to.'-'.$from.'-'.time().'.csv', 'w');
	foreach ($email_list as $email => $order) {
		$line = array_merge(array($email),$order);
		fputcsv($output,$line,",",'"');
	}
	fclose($output);
    }
?>
