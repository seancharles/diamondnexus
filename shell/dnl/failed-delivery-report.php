<?php
	ini_set('display_errors', '1');
//	require_once $_SERVER['HOME'].'magento//Mage.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	class Failed_Delivery_Report {
		function main($order_id) {
			$order = Mage::getModel('sales/order')->load($order_id);
			foreach($order->getTracksCollection() as $track) {
				if ($track->carrier_code == "fedex" && strlen($track->track_number) > 12) {
					continue;
				}
				if ($track->carrier_code == "usps" && strlen($track->track_number) < 22) {
					continue;
				}
				$trackingInfo = $track->getNumberDetail();
				if(preg_match('/attempted to deliver/i',$trackingInfo->track_summary,$trackingDate)) {
					echo $order->increment_id.",".$trackingInfo->tracking.",Failed Delivery/Notice Left";
				} elseif(preg_match('/redelivery/i',$trackingInfo->track_summary,$trackingDate)) {
					echo $order->increment_id.",".$trackingInfo->tracking.",Return to Sender Imminent";
				}
			}
		}
	}
	$report = new Failed_Delivery_Report();
	$report->main($argv[1]);
?>
