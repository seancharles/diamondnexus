<?php
namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetItemIsUsed implements ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$quoteItem = $observer->getQuoteItem();
		$product = $observer->getProduct();
		$quoteItem->setIsUsed($product->getAttributeText('is_used'));
	}
}
