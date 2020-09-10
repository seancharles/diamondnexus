<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Model\ResourceModel\CrossSell;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;

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

    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var CrossSell
     */
    protected $crossSell;

    /**
     * @var string[]
     */
    protected $matchingBandWhere = [
        'entity_varchar.value LIKE \'%Wedding Band%\'',
        'entity_varchar.value LIKE \'%Matching Band%\'',
        'entity_varchar.value = \'Miami\'',
        'entity_varchar.value = \'San Francisco\''
    ];

    /**
     * Converter constructor.
     * @param Context $context
     * @param ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param Config $eavConfig
     * @param CrossSell $crossSell
     */
    public function __construct(
        Context $context,
        ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory,
        OptionInterfaceFactory $optionInterfaceFactory,
        LinkInterfaceFactory $linkInterfaceFactory,
        Config $eavConfig,
        CrossSell $crossSell
    )
    {
        parent::__construct($context);
        $this->productCustomOptionInterfaceFactory = $productCustomOptionInterfaceFactory;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->linkFactory = $linkInterfaceFactory;
        $this->eavConfig = $eavConfig;
        $this->crossSell = $crossSell;
    }

    /**
     * @param Product $product
     * @param $optionsData
     * @param $productOptions
     */
    public function toSimple(Product $product, $optionsData, $productOptions)
    {
        $options = $product->getOptions();
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

    /**
     * @param Product $product
     * @param $optionsData
     * @param $productOptions
     * @throws LocalizedException
     */
    public function toBundle(Product $product, $optionsData, $productOptions)
    {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $product->setData('bundle_options_data', $optionsData['bundle']);
        $product->setData('bundle_selections_data', $optionsData['bundle']);
        $options = $product->getOptions();
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
        $bOptions = [];
        if (isset($optionsData['links'])) {
            $bOptions[] = $this->prepareBundleOpt('Center Stone Size', '1', $optionsData['links']);
        }
        $matchingBands = $this->getMatchingBands((int)$product->getId());
        if (count($matchingBands) > 0) {
            $optionsData['matching_bands'] =$this->prepareMatchingBandLinks($matchingBands);
            $bOptions[] = $this->prepareBundleOpt('Matching Bands', '0', $optionsData['matching_bands']);
        }
        if (count($bOptions) > 0) {
            $extensionAttributes->setBundleProductOptions($bOptions);
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

    /**
     * @param int $entityId
     * @return array
     * @throws LocalizedException
     */
    protected function getMatchingBands(int $entityId)
    {
        $select = $this->crossSell->getCrossSellSelect();
        try {
            $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, 'name')->getAttributeId();
            $select->where('main_table.parent_id = ?', $entityId)
                ->where('entity_varchar.attribute_id = ?', $attributeId)
                ->where(implode(' OR ', $this->matchingBandWhere))
                ->columns(['main_table.product_id', 'entity_varchar.value', 'entity.sku']);

        } catch (LocalizedException $e) {
            $this->_logger->critical('Can\'t get matching bands for product ID = ' . $entityId);
        }
        return $select->getConnection()->fetchAll($select);
    }

    /**
     * @param array $matchingBands
     * @return array
     */
    protected function prepareMatchingBandLinks(array $matchingBands)
    {
        $links = [];
        foreach ($matchingBands as $matchingBand) {

            /** @var \Magento\Bundle\Model\Link $link */
            $link = $this->linkFactory->create();
            $link->setSku($matchingBand['sku']);
            $link->setData('name', $matchingBand['value']);
            $link->setData('selection_qty', 1);
            $link->setData('qty', 1);
            $link->setData('can_change_qty', 1);
            $link->setData('product_id', $matchingBand['product_id']);
            $link->setData('record_id', $matchingBand['product_id']);
            $link->setIsDefault(false);
            $links[] = $link;
        }
        return $links;
    }

    protected function prepareBundleOpt(string $title, string $required, array $links)
    {
        /** @var \Magento\Bundle\Model\Option $bundleOption */
        $bundleOption = $this->optionInterfaceFactory->create();
        $bundleOption->setData([
            'title' => $title,
            'type' => 'select',
            'required' => $required,
            'product_links' => $links
        ]);
        return $bundleOption;
    }
}
