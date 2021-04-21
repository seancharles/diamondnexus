<?php
	ini_set('display_errors', '1');
//	require_once $_SERVER['HOME'].'magento//Mage.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	class Order_Status_Wrapper {
		function generateReport($order_id) {
			return shell_exec('/usr/bin/php /home/admin/shell/dnl/order-status.php '.$order_id);
		}

		function main($addTo) {
			$this->date = date('Y-m-d', strtotime('now'));
			$this->date_range = 120;
			while ($this->date_range > 0) {
				$filename = $_SERVER['HOME'].'magento//var/report/order_status_'.$this->date.'_'.$this->date_range.'.lck';
				if (file_exists($filename)) {
					$this->date_range -= 5;
				} else {
					$this->getOrders();
				}
			}
		}

		function getOrders() {
			$file_lock = $_SERVER['HOME'].'/html/var/report/order_status.lck';
			$last_date_range = fgets(fopen($file_lock, 'r'));
			$last_date_range = trim($date_range == 0 ? $this->date_range : $last_date_range);
			while ($last_date_range > 0) {
				$fromDate = date('Y-m-d H:i:s', strtotime($this->date.' - '.$last_date_range.' day'));
				$toDate = date('Y-m-d H:i:s', strtotime($this->date.' - '.($last_date_range - 5).' day'));

				$order_collection = Mage::getModel('sales/order')->getCollection()
				    ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
				    ->addFieldToFilter('status', array('in' => array('Shipped', 'delivered')));
				$date_lock = $_SERVER['HOME'].'magento//var/report/order_status_'.$this->date.'_'.$last_date_range.'.lck';
				foreach ($order_collection as $order) {
					$report_line = $this->generateReport($order->getId());
				}
				$last_date_range -= 5;
				file_put_contents($file_lock, $last_date_range);
				file_put_contents($date_lock, "");
			}
		}
	}
	$wrapper = new Order_Status_Wrapper();
	$wrapper->main($argv[1]);
?>
