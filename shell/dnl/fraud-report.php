<?php
//ini_set('display_errors', '1');
// require_once $_SERVER['HOME'].'magento//Mage.php';
Mage::app();

// Get the current store id
$storeId = Mage::app()->getStore()->getId();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);

// Date
$date      = date('Y-m-d', strtotime('now -1 day'));  // now -1 day
$fromDate = $date.' 00:00:00';
$toDate = $date.' 23:59:59';

$filename = $_SERVER['HOME'].'/html/var/report/fraud_' . $date . '.csv';

$order_collection = Mage::getModel('sales/order')->getCollection()
    ->addFieldToFilter('store_id', array('in' => array(
		'1','5','8','18',	// DN
		'12','17','13','19'	// 1215
	)))
    ->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
    ->addFieldToFilter('status', array('nin' => array('fraud', 'canceled_fraud', 'quote', 'pending')));



$bad_emails = array();
$row = 1;
// Change to some place they can upload
if (($handle = fopen($_SERVER['HOME']."/html/var/report/bad_emails.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        for ($c=0; $c < $num; $c++) {
             $bad_emails[$data[$c]] = true;
        }
        $row++;
    }
    fclose($handle);
}
$dns = Mage::getModel('customer/customer')
        ->getCollection()
        ->addAttributeToSelect('*')
        ->addFieldToFilter('group_id', 4);
foreach ($dns as $customer) {
	$bad_emails[$customer->email] = true;
}
$row = 1;
if (($handle = fopen($_SERVER['HOME']."/html/var/report/bad_addresses.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $bad_addreses[$row]['street'] = strtolower($data[0]);
        $bad_addreses[$row]['state'] = strtolower($data[1]);
        $bad_addreses[$row]['city'] = strtolower($data[2]);
        $bad_addreses[$row]['postcode'] = strtolower($data[3]);
	$row++;
    }
    fclose($handle);
}
foreach ($dns as $customer) {
	foreach ($customer->getAddresses() as $address) {
		$addr = $address->toArray();
		$bad_addreses[$row]['street'] = strtolower($addr['street']);
		$bad_addreses[$row]['state'] = strtolower($addr['state']);
		$bad_addreses[$row]['city'] = strtolower($addr['city']);
		$bad_addreses[$row]['postcode'] = strtolower($addr['postcode']);
		$row++;
	}
}
$report[0] = array("Order Id", "Order Total", "Payment Method", "Sales Person", "ASD", "Fraud Score", "Items", "New/First", "Over 499", "Over 999", "Address Mismatch", "1 day ship", "New/First & Over 299", "Do Not Sell", "Bad Email Address");

foreach ($order_collection as $order) {
	
	if( (float) $order->getTotalRefunded() == 0 ) {
	
		$transaction_collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
		   ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
		   ->addAttributeToFilter('order_id', array('eq' => $order->entity_id));
		$mp_transaction_collection = Mage::getModel('diamondnexus_multipay/transaction')->getCollection()
		   ->addFieldToFilter('timestamp', array('from'=>$fromDate, 'to'=>$toDate))
		   ->addFieldToFilter('order_id', array('eq' => $order->entity_id));
		$has_transactions = false;
		foreach ($transaction_collection as $transaction) {
		$payment_method = $order->getPayment()->getMethodInstance()->getTitle();
			$has_transactions = true;
		}
		foreach ($mp_transaction_collection as $transaction) {
			if( $transaction->getTendered() > 0 ) {
				$payment_method = "Multipay Cash";
			} elseif( strlen($transaction->getLast4()) > 0 ) {
				$payment_method = "Multipay Credit Card";
			} else {
				$payment_method = "Multipay Store Credit";
			}
			$has_transactions = true;
		}
		if (!($has_transactions)) {
		continue;
		}
        
        // initialize flag default
		$flags = [
            'items' => 0,
            'new' => 0,
            '500' => 0,
            '1000' => 0,
            'address' => 0,
            'ship' => 0,
            'NuMo' => 0,
            'DoNotSell' => 0,
            'BadEmail' => 0
        ];
        
		$fraudScore = 0;
		$new_customer = false;
		$orderItems = Mage::getModel('sales/order_item')->getCollection()
				->addFieldToFilter('order_id',array('eq' => $order->getId()))
				->addFieldToFilter('sku', array(
			array('like' => 'USLS%'),
			array('like' => 'LNXX%'),
			array('like' => 'MWXX%'),
			array('like' => 'LCXX%'),
			array('like' => 'LBXX%'),
			array('like' => 'LRRH%'),
			array('eq' => 'SHIPUP'),
			array('eq' => 'RUSHDAY'),
			array('eq' => 'RUSHNEXTDAY')
			));
		if ($orderItems->getSize() > 0) {
			$flags['items'] += 2;
			$fraudScore += 2;
		}

        // check all orders if it was the first time guest/registered combined
        $new_customer = false;
        
        // adding to handle guests who have placed order with same email
        $customer_orders_by_email = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_email', $order->getCustomerEmail());
        
        if ($customer_orders_by_email->getSize() == 1) {
            $flags['new'] += 3;
            $new_customer = true;
            $fraudScore += 3;
        }
        
        // check if customer exists by name
        $customer_orders_by_name = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_firstname', $order->getCustomerFirstname())
            ->addFieldToFilter('customer_lastname', $order->getCustomerLastname())
            ->addFieldToFilter('customer_email', $order->getCustomerEmail());
        
        if ($customer_orders_by_name->getSize() == 1) {
            $flags['new'] += 3;
            $new_customer = true;
            $fraudScore += 3;
        }
        
        if($order->customer_id > 0) {
            $customer_orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('customer_id', $order->customer_id);
                if ($customer_orders->getSize() == 1) {
                    $flags['new'] += 3;
                    $new_customer = true;
                    $fraudScore += 3;
                }
        }

		if ($order->grand_total > 499.99) {
			$flags['500'] += 1;
			$fraudScore += 1;
		}
		if ($order->grand_total > 999.99) {
			$flags['1000'] += 2;
			$fraudScore += 2;
		}
        
		$billing = array_map('trim', array_map('strtolower',$order->getBillingAddress()->getData()));
		$shipping = array_map('trim', array_map('strtolower',$order->getShippingAddress()->getData()));
		if ($shipping['region'] !== $billing['region']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['postcode'] !== $billing['postcode']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['street'] !== $billing['street']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['city'] !== $billing['city']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['country_id'] !== $billing['country_id']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['name'] !== $billing['name']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
        if ($shipping['telephone'] !== $billing['telephone']) {
		  $flags['address'] += 3;
		  $fraudScore += 3;
		}
		foreach ($bad_addreses as $bad_addy) {
		  if ($shipping['street'] == $bad_addy['street'] && $shipping['postcode'] == $bad_addy['postcode']) {
			$flags['DoNotSell'] += 6;
			$fraudScore += 6;
		  }
		  if ($billing['street'] == $bad_addy['street'] && $billing['postcode'] == $bad_addy['postcode']) {
			$flags['DoNotSell'] += 6;
			$fraudScore += 6;
		  }
		}
        // shipping methods that are more likely to be fraudulent (should be maintained with new express internalion methods)
		switch ($order->shipping_method) {
			case 'usps_pme1dah':
			case 'usps_pme1dah':
			case 'usps_pme1da':
			case 'usps_uspspo':
			case 'usps_uspsapo':

			case 'ups_upsus1da':
			case 'ups_upscaes':
			case 'ups_upscaex':
			case 'ups_upsintex':
			case 'ups_upsintes':
			
			case 'fedex_fedexus1da':
			case 'fedex_fedexus1ds':
			case 'fedex_fedexinteconomy':
			case 'fedex_fedexintexpress':
			
				$flags['ship'] += 3;
				$fraudScore += 3;

				if ($new_customer == true && $order->grand_total >= 300) {
					$flags['NuMo'] += 3;
					$fraudScore += 3;
				}
				break;
		}
		if ($order->group_id == 4) {
			$flags['DoNotSell'] += 6;
			$fraudScore += 6;
		}
		if ($bad_emails[$order->customer_email] || preg_match("@mail.com",$order->customer_email)) {
			$flags['BadEmail'] = +6;
			$fraudScore += 6;
		}
		$sales_person = Mage::getModel('admin/user')->load($order->sales_person_id)->username;
		if (empty($sales_person)) {
		$sales_person = 'Web';
		}
        // level is also referred to as "fraud score"
		if ($fraudScore > 5) {
			//print $order->increment_id." ".$order->grand_total." ".$payment_method." ".$sales_person." ".$order->anticipated_shipdate." ".$fraudScore." ".$flags['items']." ".$flags['new']." ".$flags['500']." ".$flags['1000']." ".$flags['address']." ".$flags['ship']." ".$flags['NuMo']." ".$flags['DoNotSell']." ".$flags['BadEmail']."\n";
			$report[] = array(
				$order->increment_id,
				$order->grand_total,
				$payment_method,
				$sales_person,
				$order->anticipated_shipdate,
				$fraudScore,
				($flags['items'] > 0) ? $flags['items'] : '',
                
                ($flags['new'] > 0) ? $flags['new'] : '',
                ($flags['500'] > 0) ? $flags['500'] : '',
                ($flags['1000'] > 0) ? $flags['1000'] : '',
                ($flags['address'] > 0) ? $flags['address'] : '',
                ($flags['ship'] > 0) ? $flags['ship'] : '',
                ($flags['NuMo'] > 0) ? $flags['NuMo'] : '',
                ($flags['DoNotSell'] > 0) ? $flags['DoNotSell'] : '',
                ($flags['BadEmail'] > 0) ? $flags['BadEmail'] : ''
			);
		}
    }
}
$csv=new Varien_File_Csv();
$csv->saveData($filename,$report);

$mail = new Zend_Mail();
$mail->setBodyHtml("All Fraud Report - " . $date. " \r\n")
->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
->addTo('accounting@forevercompanies.com')
->addTo('paul.baum@forevercompanies.com')
->addTo('epasek@forevercompanies.com')
->addTo('jessica.nelson@diamondnexus.com')
->setSubject('Fraud Report - ' . $date);

$content = file_get_contents($filename);
$attachment = new Zend_Mime_Part($content);
$attachment->type = mime_content_type($filename);
$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$attachment->encoding = Zend_Mime::ENCODING_BASE64;
$attachment->filename = 'fraud_' . $date . '.csv';

$mail->addAttachment($attachment);

$mail->send();
