<?php

	namespace ForeverCompanies\DynamicBundle\Observer;
	
	use Magento\Framework\Event\Observer as EventObserver;
	use Magento\Framework\Event\ObserverInterface;
	
	class AddToCart implements ObserverInterface

	{
		public function execute(\Magento\Framework\Event\Observer $observer)
		{
			$item = $observer->getEvent()->getData('quote_item');
			$item = ( $item->getParentItem() ? $item->getParentItem() : $item );
			
			$buyRequest = $item->getBuyRequest();
			
			// check if the product contains a bundled stone item for 1215 we need to adjust the bundles price
			if($buyRequest['dynamic_bundled_item_id'] > 0) {
				
				//$quoteItem->setData('name','Custom Diamond');
				//$quoteItem->setData('sku','LG100220');
				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->create('Magento\Catalog\Model\Product')->load($buyRequest['dynamic_bundled_item_id']);
				
				$price = $item->getPrice() + $product->getPrice(); //set your price here
				
				$item->setOptionByCode('dynamic_bundled_item_id', $buyRequest['dynamic_bundled_item_id']);
				$item->setCustomPrice($price);
				$item->setOriginalCustomPrice($price);
				$item->getProduct()->setIsSuperMode(true);
			}
		}
	}