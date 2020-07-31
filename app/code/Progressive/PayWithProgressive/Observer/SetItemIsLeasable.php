<?php
namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetItemIsLeasable implements ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$quoteItem = $observer->getQuoteItem();
		$product = $observer->getProduct();
		$quoteItem->setIsLeasable($product->getAttributeText('is_leasable'));
	}
}
