<?php

namespace ForeverCompanies\DynamicBundle\Plugins\Sales\Order;

class Item
{
	public function afterGetProductId(\Magento\Sales\Model\Order\Item $item, $productId)
	{
		if($item->getProduct()->getTypeId() == 'bundle')
		{
			if($item->getProduct()->getDynamicBundle() == 1) {
				// setting the product id of an order item forces magento to pull from the sales order item data
				$productId = 0;
			}
		}
		
		return $productId;
	}
}