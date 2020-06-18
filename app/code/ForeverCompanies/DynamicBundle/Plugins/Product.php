<?php

namespace ForeverCompanies\DynamicBundle\Plugins;

class Product
{
	public function afterGetPrice(\Magento\Catalog\Model\Product $product, $price)
	{
		//$productPrice = $product->getData('key: price');
		
		$price = $price + 10;
		
		return 50.00;
	}
	
	public function afterGetName(\Magento\Catalog\Model\Product $product, $name)
	{
		//$productPrice = $product->getData('key: price');
		
		$name .= " for the win.";
		
		return $name;
	}
}