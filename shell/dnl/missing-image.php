<?php

// Load Magento core
// require_once '../../app/Mage.php';
	
Mage::app();

$products = Mage::getModel('catalog/category')->setStoreId(1)->load()
	->getProductCollection()
	->addAttributeToSelect( array('id','image','small_image') )
	->addAttributeToFilter('small_image', array('eq' => ''))
	->setOrder('sku', 'ASC')
;

echo count($products);

foreach($products as $product) {

}
