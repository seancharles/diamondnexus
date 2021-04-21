<?php
	ini_set('display_errors', '1');
	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	class Order_Status_Call {
		function main($order_id) {
			$order = Mage::getModel('sales/order')->load($order_id);
			$order->_hasDataChanges = true;
		        $order->save();
		}
	}
	$report = new Order_Status_Call();
	$report->main($argv[1]);
?>
