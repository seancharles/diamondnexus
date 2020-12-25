<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\OptionValue;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

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
     * @var MatchingBand
     */
    protected $matchingBand;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductType
     */
    protected $productType;

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
     * @var string[]
     */
    protected $chainLength = [
        '12 in' => 'C',
        '13 in' => 'D',
        '14 in' => 'E',
        '15 in' => 'F',
        '16 in' => 'G',
        '17 in' => 'H',
        '18 in' => 'I',
        '19 in' => 'J',
        '20 in' => 'K',
        '21 in' => 'L',
        '22 in' => 'M',
        '23 in' => 'N',
        '24 in' => 'O'
    ];

    /**
     * Converter constructor.
     * @param Context $context
     * @param ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param Config $eavConfig
     * @param MatchingBand $matchingBand
     * @param ProductRepository $productRepository
     * @param ProductType $productType
     */
    public function __construct(
        Context $context,
        ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory,
        OptionInterfaceFactory $optionInterfaceFactory,
        LinkInterfaceFactory $linkInterfaceFactory,
        Config $eavConfig,
        MatchingBand $matchingBand,
        ProductRepository $productRepository,
        ProductType $productType
    )
    {
        parent::__construct($context);
        $this->productCustomOptionInterfaceFactory = $productCustomOptionInterfaceFactory;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->linkFactory = $linkInterfaceFactory;
        $this->eavConfig = $eavConfig;
        $this->matchingBand = $matchingBand;
        $this->productRepository = $productRepository;
        $this->productType = $productType;
    }

    /**
     * @param array $data
     * @param string $attribute
     * @return array
     */
    public function getValues(array $data, string $attribute)
    {
        $values = [];
        try {
            $source = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
            foreach ($data as $optionId) {
                $values[] = $source->getOptionText($optionId);
            }
        } catch (LocalizedException $e) {
            $this->_logger->error('Can\'t get source and attributes for ' . $attribute);
            return [];
        }
        return $values;
    }

    /**
     * @param array $data
     * @param string $attribute
     * @return array
     */
    public function getOptions(array $data, string $attribute)
    {
        $options = [];
        try {
            $source = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
            foreach ($data as $text) {
                $options[] = $source->getOptionId($text);
            }
        } catch (LocalizedException $e) {
            $this->_logger->error('Can\'t get source and attributes for ' . $attribute);
            return [];
        }
        return $options;
    }

    /**
     * @param Product $product
     * @param $optionsData
     */
    public function toSimple(Product $product, $optionsData)
    {
        $options = $product->getOptions();
        foreach ($options as &$option) {
            $option['is_require'] = 0;
        }
        if (count($optionsData['options']) > 0) {
            foreach ($optionsData['simple'] as $optionData) {
                /** @var Option $option */
                $newOption = $this->productCustomOptionInterfaceFactory->create();
                $values = $optionsData['options'][$optionData['title']];
                if ($optionData['title'] == 'Chain Length') {
                    foreach ($values as &$value) {
                        $value['sku'] = $this->chainLength[$value['title']];
                    }
                }
                $newOption->setData(
                    [
                        'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                        'title' => $optionData['title'],
                        'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                        'is_require' => 0,
                        'values' => $values,
                        'product_sku' => $product->getSku(),
                    ]
                );

                $options[] = $newOption;
            }
        }
        $product->setOptions($options);
        $this->checkOptions($product);
    }

    /**
     * @param Product $product
     * @param $optionsData
     * @param $productOptions
     * @throws NoSuchEntityException
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
            if (!isset($optionsData['options'][$attributeId])) {
                continue;
            }
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
        $this->checkOptions($product);
        $bOptions = [];
        if (isset($optionsData['links']) && count($optionsData['links'])) {
            $bOptions[] = $this->prepareBundleOpt('Center Stone Size', '1', $optionsData['links']);
        }
        $matchingBands = $this->matchingBand->getMatchingBands((int)$product->getId());
        if (count($matchingBands) > 0) {
            $optionsData['matching_bands'] = $this->prepareMatchingBandLinks($matchingBands);
            if (count($optionsData['matching_bands'] ) > 0) {
                $bOptions[] = $this->prepareBundleOpt('Matching Bands', '0', $optionsData['matching_bands']);
            }
        }
        if ($product->getSku() == 'LRENSL0091X') {
            $enhancers = $this->matchingBand->getEnhancers((int)$product->getId());
            if (count($enhancers) > 0) {
                $optionsData['enhancers'] = $this->prepareMatchingBandLinks($enhancers);
                $bOptions[] = $this->prepareBundleOpt('Enhancers', '0', $optionsData['enhancers']);
            }
        }
        if (count($bOptions) > 0) {
            $extensionAttributes->setBundleProductOptions($bOptions);
            $product->setExtensionAttributes($extensionAttributes);
        }
    }

    /**
     * @param array $values
     * @param $source
     * @param string $sku
     * @param string $label
     * @return Option
     * @throws NoSuchEntityException
     */
    public function createTotalCaratWeight(array $values, $source, string $sku, $label)
    {
        $optionValues = [];
        $products = [];
        $productLinks = $this->productRepository->get($sku)->getExtensionAttributes()->getConfigurableProductLinks();
        foreach ($productLinks as $id) {
            $products[] = $this->productRepository->getById($id);
        }
        /** @var OptionValue $value */
        foreach ($values as $value) {
            $skuLink = '';
            $priceLink = 0;
            foreach ($products as $product) {
                if ($product->getData('bundle_sku') == null || $product->getData('bundle_sku') == '') {
                    $product->setCustomAttribute('bundle_sku', $sku);
                    $product->setData('bundle_sku', $sku);
                    try {
                        $this->productRepository->save($product);
                    } catch (CouldNotSaveException $e) {
                        $this->_logger->error('Can\'t save bundle_sku ' . $product->getId() . ':' . $e->getMessage());
                    } catch (InputException $e) {
                        $this->_logger->error('Can\'t save bundle_sku ' . $product->getId() . ':' . $e->getMessage());
                    } catch (StateException $e) {
                        $this->_logger->error('Can\'t save bundle_sku ' . $product->getId() . ':' . $e->getMessage());
                    }
                }
                if ($value->getValueIndex() == $product->getData('gemstone')) {
                    $skuLink = str_replace($sku, '', $product->getSku());
                    $priceLink = $product->getPrice();
                    continue;
                }
            }
            $optionValues[] = [
                'title' => $source->getOptionText($value->getValueIndex()),
                'price' => $priceLink,
                'price_type' => "fixed",
                'sku' => $skuLink
            ];
        }
        /** @var Option $option */
        $option = $this->productCustomOptionInterfaceFactory->create();
        $option->setData(
            [
                'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                'title' => $label,
                'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                'is_require' => 0,
                'values' => $optionValues,
                'product_sku' => $sku,
            ]
        );
        return $option;
    }

    /**
     * @param $product
     */
    protected function checkOptions($product)
    {
        $options = [];
        foreach ($product->getOptions() as $option) {
            if ($option->getData('values') !== null || $option->getValues() !== null) {
                $options[] = $option;
            }
        }
        $product->setOptions($options);
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
     * @param array $matchingBands
     * @return array
     * @throws NoSuchEntityException
     */
    protected function prepareMatchingBandLinks(array $matchingBands)
    {
        $links = [];
        foreach ($matchingBands as $matchingBand) {
            /** @var Product $product */
            $product = $this->productRepository->getById($matchingBand['entity_id']);
            if ($product->isDisabled()) {
                continue;
            }
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
        $bundleOption->setData(
            [
                'title' => $title,
                'type' => 'select',
                'required' => $required,
                'product_links' => $links
            ]
        );
        return $bundleOption;
    }
}
