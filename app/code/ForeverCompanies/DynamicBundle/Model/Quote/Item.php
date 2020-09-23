<?php

declare(strict_types=1);

namespace ForeverCompanies\DynamicBundle\Model\Quote;

class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * Setup product for quote item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct($product)
    {
		$parent = $this->getParentItem();
		
		// workaround:
		// 	omit the name and sku for items that are bundled
		// 	in bundles, bundled items are managed separately
		// 	from the line item details
		if($parent) {
			$this->setData('product', $product)
				->setProductId($product->getId())
				->setProductType($product->getTypeId())
				//->setSku($this->getProduct()->getSku())
				//->setName($product->getName())
				->setWeight($this->getProduct()->getWeight())
				->setTaxClassId($product->getTaxClassId())
				->setBaseCost($product->getCost());
		} else {
			$this->setData('product', $product)
				->setProductId($product->getId())
				->setProductType($product->getTypeId())
				->setSku($this->getProduct()->getSku())
				->setName($product->getName())
				->setWeight($this->getProduct()->getWeight())
				->setTaxClassId($product->getTaxClassId())
				->setBaseCost($product->getCost());
		}
		
        if ($this->getQuote()) {
            $product->setStoreId($this->getQuote()->getStoreId());
            $product->setCustomerGroupId($this->getQuote()->getCustomerGroupId());
        }

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->setIsQtyDecimal($stockItem ? $stockItem->getIsQtyDecimal() : false);

        $this->_eventManager->dispatch(
            'sales_quote_item_set_product',
            ['product' => $product, 'quote_item' => $this]
        );

        return $this;
    }
}