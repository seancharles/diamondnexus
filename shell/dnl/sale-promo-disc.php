<?php
//ini_set('display_errors', '1');
//require_once $_SERVER['HOME'].'magento//Mage.php';
Mage::app();

// Get the current store id
$storeId = Mage::app()->getStore()->getId();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);

// Date
$date      = date('Y-m-01', strtotime('now -1 month'));  // now -1 day
$enddate      = date('Y-m-t', strtotime('now -1 month'));  // now -1 day
$fromDate = $date.' 00:00:00';
$toDate = $enddate.' 23:59:59';

$filename = $_SERVER['HOME'].'/html/var/report/sale-promo-disc__' . $date . '.csv';

$collection = Mage::getResourceModel('sales/order_shipment_collection')
	->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));

$report[0] = array("Ship/Credit Date", "Order #","SKU","Quantity","Unit List Price","Unit Discounted Price","Variance","Brand","Channel","Total Refunded", "Type");
foreach ($collection as $shipment) {
	$order = Mage::getModel('sales/order')->load($shipment->order_id);
	foreach($order->getAllItems() as $item) {
		$product = Mage::getModel('catalog/product')->load($item->product_id);
		$item_store = Mage::getModel('core/store')->load($order->store_id);

		if ((($product->price - $item->price) > 10) && ($item->price == $product->special_price)) {
			$report[] = array($shipment->created_at, $order->increment_id, $item->sku, $item->qty_ordered, $product->price, $item->price, ($item->price - $product->price), $item_store->getWebsite()->getName(), $item_store->getName(), (int)$order->total_refunded, "Sale/Promo");
		}
		elseif($item->getOriginalPrice() != $item->getPrice() && $item->getOriginalPrice() > 0) {
			$report[] = array($shipment->created_at, $order->increment_id, $item->sku, $item->qty_ordered, $product->price, $item->price, ($item->price - $product->price), $item_store->getWebsite()->getName(), $item_store->getName(), (int)$order->total_refunded, "Manual");
                }
	}
}

$csv=new Varien_File_Csv();
$csv->saveData($filename,$report);

$mail = new Zend_Mail();
$mail->setBodyHtml("All Sale/Promo/Custom Disc Report - " . $date. " \r\n")
->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
->addTo('epasek@forevercompanies.com')
->addTo('bill.tait@forevercompanies.com')
->addTo('jessica.nelson@diamondnexus.com')
->addTo('ken.licau@forevercompanies.com')
->addTo('andrew.roberts@forevercompanies.com')
->setSubject('Sale/Promo/Custom Disc Report - ' . $date);

$content = file_get_contents($filename);
$attachment = new Zend_Mime_Part($content);
$attachment->type = mime_content_type($filename);
$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
$attachment->encoding = Zend_Mime::ENCODING_BASE64;
$attachment->filename = 'sale-promo-disc_' . $date . '.csv';

$mail->addAttachment($attachment);

$mail->send();
