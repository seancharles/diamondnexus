<?php
	ini_set('display_errors', '1');
	// require_once $_SERVER['HOME'].'magento//Mage.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	class Failed_Delivery_Report_Wrapper {
		function generateReport($order_id) {
			return shell_exec('/usr/bin/php /home/admin/shell/dnl/failed-delivery-report.php '.$order_id);
		}

		function main($addTo) {
			$this->date = date('Y-m-d', strtotime('now'));
			$this->combined_file = $_SERVER['HOME'].'/html/var/report/failed_delivery_'.$this->date.'.csv';
			file_put_contents($this->combined_file, '"Order #","Tracking #","Status"'."\n");
			$date_range = 45;
			while ($date_range > 0) {
				$filename = $_SERVER['HOME'].'/html/var/report/failed_delivery_'.$this->date.'_'.$date_range.'.csv';
				if (file_exists($filename)) {
					file_put_contents($this->combined_file, file_get_contents($filename), FILE_APPEND);
					$date_range -= 5;
				} else {
					$this->getOrders();
				}
			}
			$this->mail($addTo);
		}

		function mail($addTo) {
			$mail = new Zend_Mail();
			$mail->setBodyHtml("Failing Delivery Report - " . $this->date. " \r\n")
				->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
				->addTo($addTo)
				->setSubject('Failing Delivery Report - ' . $this->date);

			$content = file_get_contents($this->combined_file);
			$attachment = new Zend_Mime_Part($content);
			$attachment->type = mime_content_type($this->combined_file);
			$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding = Zend_Mime::ENCODING_BASE64;
			$attachment->filename = 'failed_delivery_' . $this->date . '.csv';
			$mail->addAttachment($attachment);
			$mail->send();
		}

		function getOrders() {
			$file_lock = $_SERVER['HOME'].'/html/var/report/failed_delivery_report.lck';
			$date_range = fgets(fopen($file_lock, 'r'));
			$date_range = trim($date_range == 0 ? 45 : $date_range);
			while ($date_range > 0) {
				$fromDate = date('Y-m-d H:i:s', strtotime($this->date.' - '.$date_range.' day'));
				$toDate = date('Y-m-d H:i:s', strtotime($this->date.' - '.($date_range - 5).' day'));

				$order_collection = Mage::getModel('sales/order')->getCollection()
				    ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
				    ->addFieldToFilter('status', array('in' => array('Shipped', 'delivered')));
				$filename = $_SERVER['HOME'].'/html/var/report/failed_delivery_'.$this->date.'_'.$date_range.'.csv';
				$track_count = array();
				foreach ($order_collection as $order) {
					foreach($order->getTracksCollection() as $track) {
						$report_line = $this->generateReport($order->getId());
						if (strlen($report_line) > 2) {
							$report[] = explode(",",$report_line);
						}
					}
				}
				$date_range -= 5;
				file_put_contents($file_lock, $date_range);
				$csv=new Varien_File_Csv();
				$csv->saveData($filename,$report);
			}
		}
	}
	$wrapper = new Failed_Delivery_Report_Wrapper();
	$wrapper->main($argv[1]);
?>
