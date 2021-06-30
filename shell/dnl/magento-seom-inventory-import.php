<?php

$root = $_SERVER['HOME'].'magento//';

$lock_file = $root.'shell/dnl/inventory.lck';

if ( file_exists($lock_file) == true ) {
		exit;
}

$fp = fopen($lock_file,'w+');
fclose($fp);

$xml = file_get_contents('http://office.diamondnexuslabs.com/cgi-bin/web_orders.cgi?count=yar');

$inventory = new SimpleXMLElement($xml);

$aInventory = array();

if ( $inventory->children() ) {
		foreach( $inventory->count->children() as $child ) {
				$aInventory[$child->getName()] = (int) $child[0];
		}
}

// Load Magento core
// $mageFilename = $root.'app/Mage.php';
if (!file_exists($mageFilename)) {
				echo 'SETIError: Could not locate "app" directory or load Magento core files. Please check your script installation and try again.';
				exit;
}
require_once $mageFilename;

Mage::app();

$products = Mage::getModel('catalog/category')->load()
				->getProductCollection()
				->addAttributeToSelect('*')
				->addAttributeToFilter('live_seom_inventory', 1)
				//->addAttributeToFilter('status', 1)
				->setOrder('sku', 'ASC')
;

if ( count($products) == 0 ) {
				echo 'No Products to update.';
}

//echo "<pre>", print_r($aInventory), "</pre>";
//exit;

foreach($products as $product) {

		$_product = Mage::getModel("catalog/product")->load($product->getId());

		//echo $_product->getData('live_seom_inventory')."\n";

		//if ( $_product->getData('live_seom_inventory') == "1" ) {

				$sku = $_product->getSku();

				$qty = $aInventory[$sku];

				//$stockData = _$product->getStockData();

				$stock_qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();

				if ( is_numeric($qty) == true ) {

						$stockData = array();

						$stockData['qty'] = $qty;
						$stockData['is_in_stock'] = ($qty>0) ? 1 : 0;

						if ( $qty != $stock_qty  ) {

								$_product->setStockData($stockData);
								$_product->save();

								echo $sku.': Quantity updated ('.$qty.')'."\n";

						} else {
								echo $sku.': No change ('.$stock_qty.' - '.$qty.')'."\n";
						}

				} else {

						echo $sku.': Not found'."\n";

				}
		//}
}

echo 'Inventory update complete.'."\n";

unlink($lock_file);
