<?php

namespace ForeverCompanies\Profile\Helper\Product;
 
class Simple
{
		protected $cartItemFactory;
		protected $itemoption;
	
		public function __construct(
			\Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
			\Magento\Quote\Model\Quote\Item\Option $itemoption
		) {
			$this->cartItemFactory = $cartItemFactory;
			$this->itemoption = $itemoption;
		}
		
		public function formatOptions(&$itemOptions = null, $options = null)
		{
			
			
			$itemOptions['option_ids'] = implode(',',array_keys($options));
			
			foreach($options as $option)
			{
				$itemOptions['option_' . $option->id] = $option->value;
			}
		}
		
		public function setItemOptions($itemId = 0, $productId = 0, $options = null)
		{
			$itemoption = $this->itemoption;
			
			foreach($options as $key => $value)
			{
					$itemoption->unsetData();
					$itemoption->setItemId($itemId);
					$itemoption->setProductId($productId);
                    $itemoption->setCode($key);
					$itemoption->setValue($value);
                    $itemoption->save();
			}
		}
		
		public function addItem($simpleProductModel, $options)
		{
			$customOptionPrice = 0;
			$customOptionSkus = null;
			
			if ($simpleProductModel->getId()) {
				$quoteItem = $this->cartItemFactory->create();
				$quoteItem->setProduct($simpleProductModel);

				if($simpleProductModel->hasOptions() == true)
				{
					// get custom options
					$productOptions = $simpleProductModel->getOptions();

					foreach($productOptions as $option)
					{
						$values = $option->getValues();
						
						foreach($values as $valueId => $value)
						{
							// compare the option values to the selections
							if(isset($options[$option['option_id']]) == true && $options[$option['option_id']] == $valueId)
							{
								$customOptionPrice += $value->getPrice();
								
								if(strlen($value->getSku()) > 0)
								{
									$customOptionSkus .= '-' . $value->getSku();
								}
							}
						}
					}
				}
				
				$price = $simpleProductModel->getPrice() + $customOptionPrice;
				
				// set the values specific to what they need to be...
				$quoteItem->setQty(1);
				$quoteItem->setProductType('bundle');
				$quoteItem->setCustomPrice($price);
				$quoteItem->setOriginalCustomPrice($price);
				$quoteItem->setRowTotal($price);
				$quoteItem->setBaseRowTotal($price);
				$quoteItem->getProduct()->setIsSuperMode(true);

				if($customOptionSkus != null) {
					$quoteItem->setSku($simpleProductModel->getSku() . $customOptionSkus);
				}
				
				return $quoteItem;
			}
		}
}