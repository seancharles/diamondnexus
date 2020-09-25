<?php

namespace ForeverCompanies\DynamicBundle\Plugins;

class Quote
{
	public function afterGetPrice(\Magento\Quote\Model\Quote\Item $item, $price)
	{
		//mail('paul.baum@forevercompanies.com','test', print_r(get_class_methods($item),true));
		
		$bundleItemPrice = $item->getOptionByCode('bundle_price');
		
		if ( $bundleItemPrice )
		{
			$price = $price + $bundleItemPrice->getValue();
		}
		
		return $price;
	}
	
	public function afterGetFormattedOptionValue(\Magento\Quote\Model\Quote\Item $item, $option)
	{
		mail('paul.baum@forevercompanies.com','Quote::afterGetFormattedOptionValue','success');
		
		return $option;
	}
}