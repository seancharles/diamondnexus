<?php

namespace ForeverCompanies\Profile\Helper\Product;
 
class Bundle
{
		protected $cartItemFactory;
		protected $itemoption;
		
		protected $bundleSelectionProductIds;
	
		public function __construct(
			\Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
			\Magento\Quote\Model\Quote\Item\Option $itemoption
		) {
			$this->cartItemFactory = $cartItemFactory;
			$this->itemoption = $itemoption;
		}
		
		public function setBundleSelectionProductIds($array)
		{
			$this->bundleSelectionProductIds = $array;
		}
		
		/**
		* format bundle custom options
		*/
		public function formatBundleOptionsParent(&$itemOptions = null, $options = null)
		{
			$itemOptions['option_ids'] = implode(',',array_keys($options));
			
			foreach($options as $option)
			{
				$itemOptions['option_' . $option->id] = $option->value;
			}
		}
		
		public function formatBundleOptionIds(&$itemSelections = null, $selections = null)
		{
			$optionIds = array();
			
			foreach($selections as $key => $value)
			{
				$optionIds[] = $key;
			}
			
			$itemSelections['bundle_option_ids'] = '[' . implode(",", $optionIds) . ']';
		}
		
		public function formatBundleSelectionsParent(&$itemSelections = null)
		{
			$selectionIds = array();
			
			foreach($this->bundleSelectionProductIds as $key => $value)
			{
				$selectionIds[] = '"' . $key . '"';
				
				$itemSelections['selection_qty_' . $key] = '1';
				$itemSelections['product_qty_' . $value['product_id']] = '1';
			}
			
			$itemSelections['bundle_selection_ids'] = '[' . implode(",", $selectionIds)  . ']';
		}
		
		public function formatBundleSelectionsChild(&$itemSelections = null, $selectionId = null, $selection = null)
		{
			$itemSelections['selection_id'] = $selectionId;
			
			$itemSelections['bundle_selection_attributes'] = json_encode([
				'price' => 1,
				'qty' => 1,
				'option_label' => $selection['option_title'],
				'option_id' => $selection['option_id']
			]);
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
		
		public function addParentItem($bundleProductModel, $options, $childProductModel)
		{
			$selectionPrice = 0;
			$customOptionPrice = 0;
			$customOptionSkus = null;
			
			if ($bundleProductModel->getId()) {
				$quoteItem = $this->cartItemFactory->create();
				$quoteItem->setProduct($bundleProductModel);

				if($bundleProductModel->hasOptions() == true)
				{
					// get custom options
					$productOptions = $bundleProductModel->getOptions();

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
				
				// iterate through native bundle options
				foreach($this->bundleSelectionProductIds as $bundle)
				{
					$selectionPrice += $bundle['price'];
				}
				
				if(isset($childProductModel) == true && $childProductModel->getId() > 0) {
					// add the dynamic bundled item price to the selection total only when dynamic bundling
					$price = $bundleProductModel->getPrice() + $childProductModel->getPrice() + $customOptionPrice + $selectionPrice;
				} else {
					// native pricing
					$price = $bundleProductModel->getPrice() + $customOptionPrice + $selectionPrice;
				}
				
				// set the values specific to what they need to be...
				$quoteItem->setQty(1);
				$quoteItem->setProductType('bundle');
				$quoteItem->setCustomPrice($price);
				$quoteItem->setOriginalCustomPrice($price);
				$quoteItem->setRowTotal($price);
				$quoteItem->setBaseRowTotal($price);
				$quoteItem->getProduct()->setIsSuperMode(true);

				if($customOptionSkus != null) {
					$quoteItem->setSku($bundleProductModel->getSku() . $customOptionSkus);
				}
				
				return $quoteItem;
			}
		}
		
		public function addChildItem($childProductModel, $parentId = 0, $options)
		{
			$customOptionPrice = 0;
			$customOptionSkus = null;
			
			if ($childProductModel->getId()) {
				$quoteItem = $this->cartItemFactory->create();
				$quoteItem->setProduct($childProductModel);
				
				if($childProductModel->hasOptions() == true)
				{
					// get custom options
					$productOptions = $childProductModel->getOptions();

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
				
				// implement the bundle price preference
				if($childProductModel->getBundlePrice() != null) {
					$price = $childProductModel->getBundlePrice() + $customOptionPrice;
				} else {
					$price = $childProductModel->getPrice() + $customOptionPrice;
				}

				// implement the bundle sku preference
				if(strlen($childProductModel->getBundleSku()) > 0) {
					$quoteItem->setSku($childProductModel->getBundleSku() . $customOptionSkus);
				} else {
					$quoteItem->setSku($childProductModel->getSku() . $customOptionSkus);
				}
				
				// set the values specific to what they need to be...
				$quoteItem->setParentItemId($parentId);
				$quoteItem->setName($childProductModel->getName());
				$quoteItem->setQty(1);
				$quoteItem->setProductType('simple');
				$quoteItem->setCustomPrice($price);
				$quoteItem->setOriginalCustomPrice($price);
				$quoteItem->setRowTotal($price);
				$quoteItem->setBaseRowTotal($price);
				//$quoteItem->getProduct()->setIsSuperMode(true);
				
				return $quoteItem;
			}
		}
		
		/**
		 * get all the selection products used in bundle product
		 * @param $product
		 * @return mixed
		 */
		public function getBundleOptions($product)
		{
			$selectionCollection = $product->getTypeInstance()
				->getSelectionsCollection(
					$product->getTypeInstance()->getOptionsIds($product),
					$product
				);
			$bundleOptions = [];
			foreach ($selectionCollection as $selection) {
				$bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
			}
			return $bundleOptions;
		}
		
		public function formatBundleOptionSelection()
		{
			$bundleOptions = array();
			
			foreach($this->bundleSelectionProductIds as $selectionId => $bundeOption)
			{
				$bundleOptions[$bundeOption['option_id']] = "{$selectionId}";
			}
			
			return $bundleOptions;
		}
}