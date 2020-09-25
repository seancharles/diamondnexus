<?php

namespace ForeverCompanies\DynamicBundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel;

class Composite extends \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\Composite
{
    public function modifyData(array $data)
    {

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        $isBundleProduct = $product->getTypeId() === Type::TYPE_CODE;
        if ($isBundleProduct && $modelId) {
            $data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS] = [];

            /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
            foreach ($this->optionsRepository->getList($product->getSku()) as $option) {
                $selections = [];
                foreach ($option->getProductLinks() as $productLink) {
                    $linkedProduct = $this->productRepository->get($productLink->getSku());
                    $integerQty = 1;
                    if ($linkedProduct->getExtensionAttributes()->getStockItem()) {
                        if ($linkedProduct->getExtensionAttributes()->getStockItem()->getIsQtyDecimal()) {
                            $integerQty = 0;
                        }
                    }
                    $selections[] = [
                        'selection_id' => $productLink->getId(),
                        'option_id' => $productLink->getOptionId(),
                        'product_id' => $linkedProduct->getId(),
                        'name' => $linkedProduct->getName(),
                        'sku' => $linkedProduct->getSku(),
                        'is_default' => ($productLink->getIsDefault()) ? '1' : '0',
                        'selection_price_value' => $productLink->getPrice(),
                        'selection_price_type' => $productLink->getPriceType(),
                        'selection_qty' => (bool)$integerQty ? (int)$productLink->getQty() : $productLink->getQty(),
                        'selection_can_change_qty' => $productLink->getCanChangeQuantity(),
                        'selection_qty_is_integer' => (bool)$integerQty,
                        'position' => $productLink->getPosition(),
                        'delete' => '',
                    ];
                }

                $data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS][] = [
                    'position' => $option->getPosition(),
                    'option_id' => $option->getOptionId(),
                    'title' => $option->getTitle(),
                    'is_dynamic_selection' => $option->getIsDynamicSelection(), //new field
                    'default_title' => $option->getDefaultTitle(),
                    'type' => $option->getType(),
                    'required' => ($option->getRequired()) ? '1' : '0',
                    'bundle_selections' => $selections,
                ];
            }
        }

        return $data;
    }
}