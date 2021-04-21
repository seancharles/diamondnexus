<?php

$xml = file_get_contents('http://office.diamondnexuslabs.com/cgi-bin/web_orders.cgi?count=yar');

$inventory = new SimpleXMLElement($xml);

$aInventory = array();

if ( $inventory->children() ) {
		foreach( $inventory->count->children() as $child ) {
				$aInventory[$child->getName()] = (int) $child[0];
		}
}

// Load Magento core
// require_once '../../app/Mage.php';

Mage::app();

$products = Mage::getModel('catalog/category')->load()
				->getProductCollection()
				->addAttributeToSelect(array('id','sku'))
				->addAttributeToFilter('live_seom_inventory', 1)
				->addAttributeToFilter('status', 1)
				->setOrder('sku', 'ASC')
;

if ( count($products) == 0 ) {
				echo 'No Products to update.';
}

foreach($products as $product) {

	$stockItem = Mage::getModel('cataloginventory/stock_item');

	$stockItem->setData(array());
	$stockItem->loadByProduct($product->getId())->setProductId($product->getId());

	$sku = $product->getSku();

	$qty = $aInventory[$sku];

	$stock_qty = $stockItem->getQty();

	if ( is_numeric($qty) == true ) {

			if ( $qty != $stock_qty  ) {

					$stockItem->setDataUsingMethod('qty', $qty);
					$stockItem->setDataUsingMethod('is_in_stock', ($qty>0) ? 1 : 0);
					$stockItem->save();

					echo $sku.': Quantity updated ('.$qty.')'."\n";

			} else {
					echo $sku.': No change ('.$stock_qty.' - '.$qty.')'."\n";
			}
			
	} else {

		echo $sku.': Not found'."\n";

	}
}

echo 'Inventory update complete.'."\n";


