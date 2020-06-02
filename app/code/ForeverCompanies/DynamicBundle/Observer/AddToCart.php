<?php

	namespace ForeverCompanies\DynamicBundle\Observer;

	use Magento\Framework\Event\ObserverInterface;
	
	use Magento\Catalog\Model\Product;
	use Magento\Checkout\Model\Cart;
	
	class AddToCart implements ObserverInterface

	{
		protected $_objectManager;

		public function __construct(
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Checkout\Model\Cart $cart,
			\Magento\Catalog\Model\Product $product,
			\Magento\Framework\ObjectManagerInterface $interface,
			\Magento\Quote\Model\Quote\Item $quote
		) {
			$this->_objectManager = $objectManager;
			$this->cart = $cart;
			$this->product = $product;
			$this->objectManager = $interface;
			$this->quote = $quote;
		}
		public function execute(\Magento\Framework\Event\Observer $observer)
		{
			$product = $observer->getProduct();
			$quoteItem = $observer->getQuoteItem();
			
			// update 1215 specific settings to include stone price
			if ($product->getAttributeSetId() == 9)
			{
				$quoteItem->setData('row_total', 810.0);
				$quoteItem->setData('base_row_total', 810.0);
			}
			elseif ($product->getAttributeSetId() == 13)
			{
				//$quoteItem->setLiveDiamond(true);
				
				$quoteItem->setData('name','Custom Diamond');
				$quoteItem->setData('sku','LG100220');
				
				
				//$item->setOptionByCode('diamond_price',100.00);
				//$item->setOptionByCode('diamond_price',100.00);
				
				//mail('paul.baum@forevercompanies.com','item options', print_r($quoteItem->getOptions(), true) );
			}
		}
	}