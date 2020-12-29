<?php

namespace ForeverCompanies\CustomAttributes\Model\Bundle\Product;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Api\Data\ProductInterface;

class OptionList extends \Magento\Bundle\Model\Product\OptionList
{
    /**
     * @param Product|ProductInterface $product
     * @return OptionInterface[]
     */
    public function getItems(ProductInterface $product)
    {
        $optionCollection = $this->type->getOptionsCollection($product);
        $this->extensionAttributesJoinProcessor->process($optionCollection);
        $optionList = [];
        /** @var Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId());
            /** @var Option $optionDataObject */
            $optionDataObject = $this->optionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionDataObject,
                $option->getData(),
                OptionInterface::class
            );
            $optionDataObject->setOptionId($option->getOptionId())
                ->setTitle(
                    $option->getTitle() === null ?
                    $option->getData('default_title')
                    : $option->getTitle()
                )
                ->setData('default_title', $option->getData('default_title'))
                ->setSku($product->getSku())
                ->setData('bundle_customization_type', $option->getData('bundle_customization_type'))
                ->setData('is_dynamic_selection', $option->getData('is_dynamic_selection'))
                ->setProductLinks($productLinks);
            $optionList[] = $optionDataObject;
        }
        return $optionList;
    }
}
