<?php
	ini_set('display_errors', '1');
	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	Mage::app();
	# set the time zone to CST
	date_default_timezone_set('America/Chicago'); 

	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

        $fromDate = date('Y-m-d H:i:s', strtotime('now -120 day'));
       	$toDate = date('Y-m-d H:i:s');

 	$order_collection = Mage::getModel('sales/order')->getCollection()
		->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
		->addFieldToFilter('status', array('in' => array('Shipped')));
	$no_move = array();
	foreach ($order_collection as $order) {
		$shipped_date = Mage::getResourceModel('sales/order_status_history_collection')
			->addAttributeToSelect('created_at')
			->addAttributeToFilter('status', array('eq'=>'Shipped'))
			->addAttributeToFilter('parent_id', array('eq' => $order->getId()))->load();
			foreach ($shipped_date as $dd) {
				$ship_date = $dd->created_at;
				if ($ship_date > strtotime("-12 days")) {
					print_r($order->increment_id." ".$order->status." ".$ship_date."\n");
					$no_move[] = array('increment_id' => $order->increment_id, 'order_status' => $order->status, 'ship_date' => $ship_date);
				}
				break;
			}

	}

        if( count($no_move) > 0 ) {

                mail(
                        '2624080870@txt.att.net,'. // Paul B
                        '4146280757@vtext.com,'. // Edie P
                        '4143059315@vtext.com', // Charles W
                        'Critical: Slow Order Ship Issue',
                        "$row->count  Events not imported for more than 15 days"
                );

			$body = '<table border="1">'."\r\n";
			$body .= '<tr><th>Order ID</th><th>Ship Date</th><th>Order Status</th></tr>'."\r\n";

			foreach ($no_move as $item) {
				$body .= '<tr><td>'.$item['increment_id'].'</td><td>'.$item['ship_date'].'</td><td>'.$item['order_status'].'</td></tr>'."\r\n";
			}

			$body .= '</table>'."\r\n";

			mail(
					'it@forevercompanies.com',
					'Critical: Slow Order Ship Issue',
					$body,
					'Content-type:text/html'
			);
        }

	echo("Notification script done..." . "\n");
