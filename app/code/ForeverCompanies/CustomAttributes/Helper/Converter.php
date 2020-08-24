<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;

class Converter extends AbstractHelper
{
    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    protected $productCustomOptionInterfaceFactory;

    /**
     * @var OptionInterfaceFactory
     */
    protected $optionInterfaceFactory;

    public function __construct(
        Context $context,
        ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory,
        OptionInterfaceFactory $optionInterfaceFactory
    ) {
        parent::__construct($context);
        $this->productCustomOptionInterfaceFactory = $productCustomOptionInterfaceFactory;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
    }

    public function toSimple(Product $product, $optionsData, $productOptions)
    {
        $options = [];
        foreach ($optionsData['simple'] as $optionData) {
            /** @var Option $option */
            $option = $this->productCustomOptionInterfaceFactory->create();
            $option->setData(
                [
                    'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                    'title' => $optionData['title'],
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'is_require' => 1,
                    'values' => $optionsData['options'][$optionData['title']],
                    'product_sku' => $product->getSku(),
                ]
            );
            $options[] = $option;
        }
        $product->setOptions($options);
    }

    public function toBundle(Product $product, $optionsData, $productOptions)
    {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $product->setData('bundle_options_data', $optionsData['bundle']);
        $product->setData('bundle_selections_data', $optionsData['bundle']);
        $options = [];
        foreach ($product->getData('bundle_options_data') as $optionData) {
            /** @var Option $option */
            $option = $this->productCustomOptionInterfaceFactory->create();
            $attributeId = $this->getAttributeIdFromProductOptions($productOptions, $optionData['title']);
            $option->setData(
                [
                    'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                    'title' => $optionData['title'],
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'is_require' => 1,
                    'values' => $optionsData['options'][$attributeId],
                    'product_sku' => $product->getSku(),
                ]
            );
            $options[] = $option;
        }
        $product->setOptions($options);
        if (isset($optionsData['links'])) {
            /** @var \Magento\Bundle\Model\Option $bundleOption */
            $bundleOption = $this->optionInterfaceFactory->create();
            $bundleOption->setData([
                'title' => 'Center Stone Size',
                'type' => 'select',
                'required' => '1',
                'product_links' => $optionsData['links']
            ]);
            $extensionAttributes->setBundleProductOptions([$bundleOption]);
            $product->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * @param array $options
     * @param string $title
     * @return false|mixed|string|null
     */
    protected function getAttributeIdFromProductOptions(array $options, string $title)
    {
        /** @var Attribute $option */
        foreach ($options as $option) {
            if ($option->getLabel() == $title) {
                return $option->getAttributeId();
            }
        }
        return false;
    }
}
