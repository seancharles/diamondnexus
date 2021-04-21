<?php

	ini_set("display_errors", true);

	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	
	Mage::app();
	
	$storesConfig = [
		1 => [1, 5, 8, 18],
		2 => [11, 14, 15, 20],
		3 => [12, 17, 13, 19]
	];
	
	$carrierMapping = [
		'Federal Express' => 'FEDEX',
		'Fedex - US - 1-Day' => 'FEDEX',
		'Fedex - US - 1-Day S' => 'FEDEX',
		'Fedex - US - 2-Day' => 'FEDEX',
		'Fedex - US - 2-Day S' => 'FEDEX',
		'Fedex - Int - Express' => 'FEDEX',
		'Fedex - Int - Economy' => 'FEDEX',
		'USPS - PM - Ground' => 'USPS',
		'USPS - PME - 1-Day' => 'USPS',
		'USPS - Priority Mail' => 'USPS',
		'United Parcel Service' => 'UPS'
	];
	
	// allows script to be run in production or sandbox, values "yes" and "no"
	$sandboxMode = $argv[1];
	
	// get system config values to make API connection
	function getApiConfig($storeId=0)
	{
		return [
			'cleint_id' => Mage::getStoreConfig('diamondnexus_paypal/pay_now/client_id'),
			'cleint_secret' => Mage::getStoreConfig('diamondnexus_paypal/pay_now/client_secret')
		];
	}
	
	function getShipments($storeIdList=null, $days=2)
	{
		$date = date("Y-m-d", strtotime("-{$days} days"));
		
		$shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
		
		$shipmentCollection->addAttributeToFilter('store_id', array('in' => $storeIdList));
		$shipmentCollection->addAttributeToFilter('created_at', array('gt' => $date . " 00:00:00"));
		
		return $shipmentCollection;
	}

	try{
		
		$paypalTackingAPI = new Paypal_Tracking($sandboxMode);
		
		foreach($storesConfig as $websiteId => $storeIds)
		{
			// get store config
			$config = getApiConfig($websiteId);
			
			// get store specific token
			$apiToken = $paypalTackingAPI->getToken($config['cleint_id'], $config['cleint_secret']);
			
			$trackingList = [];
			
			$batch = 0;

			if($apiToken)
			{
				// get all shipments for specific store in last 90 days
				$shipmentCollection = getShipments($storeIds, 2);
				
				foreach($shipmentCollection as $shipment)
				{
					$payment = $shipment->getOrder()->getPayment();
					
					// only send for orders paid with Paypal Express
					if($payment->getMethod() == 'paypal_express')
					{
						foreach ($shipment->getAllTracks() as $track) {
							
							/*
								$tracker = array(
									'transaction_id' => $transactionId,
									'tracking_number' => $tackingNum,
									'status' => 'SHIPPED',
									'carrier' => 'FEDEX',
									'notify_buyer' => false
								);
							*/
							
							$trackingDetails = [
								'transaction_id' => $payment->getLastTransId(),
								'tracking_number' => $track->getTrackNumber(),
								'status' => 'SHIPPED',
								'carrier' => $carrierMapping[$track->getTitle()],
								'notify_buyer' => false
							];
							
							print_r($trackingDetails) . "\n";
							
							$trackingList[$batch][] = $trackingDetails;
							
							if(count($trackingList[$batch]) >= 20)
							{
								$batch++;
							}
						}
					}
				}
				
				// process list in batch with max 20 items
				foreach($trackingList as $batch) {
					print_r($paypalTackingAPI->sendTracking(
						$batch,
						$apiToken
					));
				}
				
			} else {
				echo "Unable to connect to Paypal API\n";
			}
		}
		
	} catch (Exception $e) {
		echo $e->getMessage() . "\n";
	}