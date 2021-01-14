<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\DynamicBundle\Model\Product;

/**
 * Bundle Type Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Type extends  \Magento\Bundle\Model\Product\Type
{
    /**
     * Check if product can be bought
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkProductBuyState($product)
    {
        /*
            TODO: This should be implemented for dynamic bundle add to cart
        
            parent::checkProductBuyState($product);
        
            $productOptionIds = $this->getOptionsIds($product);
            $productSelections = $this->getSelectionsCollection($productOptionIds, $product);
            $selectionIds = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = $this->serializer->unserialize($selectionIds->getValue());
            $buyRequest = $product->getCustomOption('info_buyRequest');
            $buyRequest = new \Magento\Framework\DataObject($this->serializer->unserialize($buyRequest->getValue()));
            $bundleOption = $buyRequest->getBundleOption();

            if (empty($bundleOption)) {
                throw new \Magento\Framework\Exception\LocalizedException($this->getSpecifyOptionMessage());
            }

            $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
            foreach ($selectionIds as $selectionId) {
                $selection = $productSelections->getItemById($selectionId);
                if (!$selection || !$selection->isSalable() && !$skipSaleableCheck) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The required options you selected are not available.')
                    );
                }
            }

            $product->getTypeInstance()
                ->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            foreach ($optionsCollection->getItems() as $option) {
                if ($option->getRequired() && empty($bundleOption[$option->getId()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please select all required options.'));
                }
            }
        */

        return $this;
    }
}
