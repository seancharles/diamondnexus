<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class Mapping extends AbstractHelper
{
    /**
     * @var array
     */
    protected $mappingSku = [
        'Precious Metal' => [
            '10K White Gold' => '10WX',
            '10K Yellow Gold' => '10YX',
            '14K Yellow Gold & Platinum Plating' => '14DX',
            '14K Yellow Gold/Platinum Plating' => '14DX',
            '14K Rose/White Gold' => '14RW',
            '14K Rose Gold' => '14RX',
            '14K Rose/Yellow Gold' => '14RY',
            '14K White/Rose Gold' => '14WR',
            '14K White Gold' => '14WX',
            '14K White/Yellow Gold' => '14WY',
            '14K White/Yellow/Rose Gold' => '14WYRX',
            '14K Yellow/Rose Gold' => '14YR',
            '14K Yellow/White Gold' => '14YW',
            '14K Yellow Gold' => '14YX',
            '18K Rose/White Gold' => '18RW',
            '18K Rose Gold' => '18RX',
            '18K Rose/Yellow Gold' => '18RY',
            '18K White/Rose Gold' => '18WR',
            '18K White Gold' => '18WX',
            '18K White/Yellow Gold' => '18WY',
            '18K Yellow/Rose Gold' => '18YR',
            '18K Yellow/White Gold' => '18YW',
            '18K Yellow Gold' => '18YX',
            'Black Zirconium' => 'BZXX',
            'Cobalt' => 'COXX',
            'Damascus' => 'DMXX',
            'Lorian Platinum Tutone' => 'LP2U',
            'Lorian Platinum & Rose Gold' => 'LPRG',
            'Lorian Platinum and Rose Gold' => 'LPRG',
            'Lorian Platinum' => 'LPXX',
            'Lorian Platinum & Yellow Gold' => 'LPYG',
            'Lorian Platinum and Yellow Gold' => 'LPYG',
            'Palladium' => 'PDXX',
            'Platinum' => 'PTXX',
            'Silver & 14K Rose Gold	' => 'SSRX',
            'Silver and 14K Rose Gold	' => 'SSRX',
            'Sterling Silver' => 'SSXX',
            'Silver & 14K Yellow Gold' => 'SSYX',
            'Silver and 14K Yellow Gold' => 'SSYX',
            'Tungsten' => 'TNXX',
            'Titanium' => 'TTXX'
        ],
        'Certified Stone' => [
            'Classic Stone' => '0',
            'Certified Stone' => '1'
        ],
        'Stone Shape' => [
            'Antique Rose Asscher' => 'RA',
            'Antique Rose Cushion' => 'RC',
            'Antique Rose Oval' => 'RO',
            'Antique Rose Pear' => 'RP',
            'Antique Rose Princess(SQUARE)' => 'RS',
            'Antique Rose Round' => 'RR',
            'Asscher' => 'AS',
            'Cushion' => 'CU',
            'Emerald' => 'EM',
            'Heart' => 'HT',
            'Marquise' => 'MQ',
            'Multi Cuts' => 'ML',
            'Octagon' => 'OC',
            'Oval' => 'OV',
            'Pear' => 'PR',
            'Princess' => 'PC',
            'Radiant' => 'RA',
            'Round Brilliant' => 'RB',
            'Straight Baguette' => 'SB',
            'Tapered Baguette' => 'TB',
            'Triangle' => 'TR',
            'Trillion' => 'TL'
        ],
        'Color' => [
            'Black Multi' => 'BLML',
            'Black Pearl' => 'BLPR',
            'Black & White' => 'BLWH',
            'Black White' => 'BLWH',
            'Black' => 'BLXX',
            'Blue Topaz' => 'BUTZ',
            'CocoBollo & Damascus' => 'CBDM',
            'CocoBollo Damascus' => 'CBDM',
            'CocoBollo & Titanium' => 'CBTX',
            'CocoBollo Titanium' => 'CBTX',
            'Charcoal & Titanium' => 'CCTX',
            'Charcoal Titanium' => 'CCTX',
            'Chocolate Multi' => 'CHML',
            'Chocolate & White	' => 'CHWH',
            'Chocolate White' => 'CHWH',
            'Chocolate' => 'CHXX',
            'Champagne & Chocolate' => 'CPCH',
            'Champagne Chocolate' => 'CPCH',
            'Champagne Multi' => 'CPML',
            'Champagne & White' => 'CPWH',
            'Champagne White' => 'CPWH',
            'Champagne' => 'CPXX',
            'Cross Satin Black' => 'CRSB',
            'Cross Satin Silver' => 'CRSS',
            'Cross Satin' => 'CRSX',
            'Emerald & White' => 'EMWH',
            'Emerald White' => 'EMWH',
            'Emerald' => 'EMXX',
            'Fiji Orangewood & Black Zirconium' => 'FOBZ',
            'Fiji Orangewood Black Zirconium' => 'FOBZ',
            'Glacial Ice & White' => 'GIWH',
            'Glacial Ice White' => 'GIWH',
            'Glacial Ice' => 'GIXX',
            'Hammer' => 'HMXX',
            'Meteorite' => 'MEXX',
            'Multi Topaz' => 'MLTZ',
            'Multi Color' => 'MLXX',
            'New Canary Multi' => 'NCML',
            'Canary & Sapphire' => 'NCSP',
            'Canary Sapphire' => 'NCSP',
            'Canary & White' => 'NCWH',
            'Canary White' => 'NCWH',
            'Canary' => 'NCXX',
            'Pink Topaz' => 'PKTZ',
            'Red Topaz' => 'RDTZ',
            'Rose & White' => 'RSWH',
            'Rose White' => 'RSWH',
            'Rose' => 'RSXX',
            'Ruby & White' => 'RUWH',
            'Ruby White' => 'RUWH',
            'Ruby' => 'RUXX',
            'Rosewood & Titanium' => 'RXTX',
            'Rosewood Titanium' => 'RXTX',
            'Sapphire & Canary' => 'SPNC',
            'Sapphire Canary' => 'SPNC',
            'Sapphire & White' => 'SPWH',
            'Sapphire White' => 'SPWH',
            'Sapphire' => 'SPXX',
            'White & Black' => 'WHBL',
            'White Black' => 'WHBL',
            'White & Chocolate' => 'WHCH',
            'White Chocolate' => 'WHCH',
            'White & Champagne' => 'WHCP',
            'White Champagne' => 'WHCP',
            'White & Emerald' => 'WHEM',
            'White Emerald' => 'WHEM',
            'White & Glacial Ice' => 'WHGI',
            'White Glacial Ice' => 'WHGI',
            'White Multi' => 'WHML',
            'White & New Canary' => 'WHNC',
            'White New Canary' => 'WHNC',
            'White Pearl' => 'WHPR',
            'Whit & Rose' => 'WHRS',
            'Whit Rose' => 'WHRS',
            'White & Ruby' => 'WHRU',
            'White Ruby' => 'WHRU',
            'White & Sapphire' => 'WHSP',
            'White Sapphire' => 'WHSP',
            'White Topaz' => 'WHTZ',
            'White' => 'WHXX',
            'None' => 'XXXX',
            'Yellow Topaz' => 'YLTZ'
        ]
    ];

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Link
     */
    protected $linkHelper;

    /**
     * @var ProductFunctional
     */
    protected $productFunctionalHelper;

    /**
     * @var Converter
     */
    protected $converterHelper;

    /**
     * @var MatchingBand
     */
    protected $matchingBandHelper;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Link $linkHelper
     * @param ProductFunctional $productFunctionalHelper
     * @param Converter $converterHelper
     * @param MatchingBand $matchingBandHelper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductRepositoryInterface $productRepository,
        Link $linkHelper,
        ProductFunctional $productFunctionalHelper,
        Converter $converterHelper,
        MatchingBand $matchingBandHelper,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productRepository = $productRepository;
        $this->linkHelper = $linkHelper;
        $this->productFunctionalHelper = $productFunctionalHelper;
        $this->converterHelper = $converterHelper;
        $this->matchingBandHelper = $matchingBandHelper;
        $this->customLogger = $logger;
    }

    /**
     * @param Product $product
     * @param array $productOptions
     * @return array|boolean
     */
    public function prepareOptionsForBundle(Product $product, array $productOptions)
    {
        /** @var Configurable $configurable */
        $configurable = $product->getTypeInstance();
        $data = [];
        $type = $this->getTypeOfProduct($productOptions, $product);
        if (!$type) {
            $this->customLogger->error('Product ID = ' . $product->getId() . ' can\'t transform to bundle');
            return false;
        }
        if ($type == Type::TYPE_SIMPLE) {
            $data = $this->prepareProductToSimple($product, $productOptions);
        }
        if ($type == Type::TYPE_BUNDLE) {
            $data = $this->prepareProductToBundle($product, $productOptions, $configurable);
        }
        $product->setQty($this->prepareQty($product, $configurable));
        return $data;
    }

    /**
     * @param Product $product
     * @param Configurable $configurable
     * @return float
     */
    protected function prepareQty(Product $product, Configurable $configurable)
    {
        $countOfProducts = $product->getQty();
        $usedProducts = $configurable->getUsedProducts($product);
        /** @var Product $usedProduct */
        foreach ($usedProducts as $usedProduct) {
            $this->productFunctionalHelper->addProductToDelete($usedProduct);
            $qty = $usedProduct->getQty();
            if ($countOfProducts == 0) {
                $countOfProducts = $qty;
            }
            if ($countOfProducts > $qty) {
                $this->customLogger->info('Different qty products in Product SKU = ' . $product->getSku());
                $countOfProducts = $qty;
            }
        }
        return $countOfProducts;
    }

    /**
     * @param Product $product
     * @param array $productOptions
     * @return array[]
     */
    protected function prepareProductToSimple(Product $product, array $productOptions)
    {
        $product->setTypeId(Type::TYPE_SIMPLE);
        $customizableOptions = [];
        $simpleOptions = [];
        $options = [];
        foreach ($productOptions as $productOption) {
            $simpleOptions[] = $this->getOption($productOption);
            $options[$productOption['label']] = $this->prepareOptions($productOption);
            if ($productOption['label'] == 'Precious Metal') {
                $product->setData('metal_type', null);
            }
            if ($productOption['label'] == 'Certified Stone') {
                $product->setData('certified_stone', null);
            }
        }
        foreach ($options as $attributeId => $option) {
            foreach ($option as $attribute => $index) {
                $customizableOption = [
                    'title' => $index,
                    'price' => 0,
                    'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                    'sku' => $this->getSkuForOption($attributeId, $index),
                    'product_sku' => $product->getSku(),
                ];
                $customizableOptions[$attributeId][] = $customizableOption;
            }
        }
        return ['simple' => $simpleOptions, 'options' => $customizableOptions];
    }

    /**
     * @param Product $product
     * @param array $productOptions
     * @param Configurable $configurable
     * @return array[]
     */
    protected function prepareProductToBundle(Product $product, array $productOptions, Configurable $configurable)
    {
        $classicStone = false;
        $product->setTypeId(Type::TYPE_BUNDLE);
        $bundleOptions = [];
        $options = [];
        $sku = $product->getSku();
        foreach ($productOptions as $productOption) {
            try {
                $productAttribute = $this->productAttributeRepository->get($productOption->getAttributeId());
                $attrCode = $productAttribute->getAttributeCode();
                if ($attrCode !== 'gemstone') {
                    $bundleOptions[] = $this->getOption($productOption);
                    $options[$productOption['attribute_id']] = $this->prepareOptions($productOption);
                    if ($productOption['label'] == 'Precious Metal') {
                        $product->setData('metal_type', null);
                    }
                    if ($productOption['label'] == 'Certified Stone') {
                        $product->setData('certified_stone', null);
                    }
                } else {
                    $optionLabel = $productOption->getLabel();
                    if ($optionLabel == 'Total Carat Weight') {
                        $values = $productOption->getValues();
                        $source = $productAttribute->getSource();
                        $tcw = $this->converterHelper->createTotalCaratWeight($values, $source, $sku, $optionLabel);
                        $oldOptions = $product->getOptions();
                        $oldOptions[] = $tcw;
                        $product->setOptions($oldOptions);
                        continue;
                    }
                    if ($optionLabel == 'Center Stone Size') {
                        $bundleOptions[] = $this->getOption($productOption);
                        $options[$productOption['attribute_id']] = $this->prepareOptions($productOption);
                    }
                    /** @var ProductExtension $extensionAttributes */
                    $extensionAttributes = $product->getExtensionAttributes();
                    $configurableProductLinks = $extensionAttributes->getConfigurableProductLinks();
                    $basePrice = $product->getPriceInfo()->getPrice('base_price')->getValue();
                    $links = $this->prepareLinksForBundle($configurableProductLinks, $basePrice, $sku);
                    $product->setData('carat_weight', null);
                }
            } catch (NoSuchEntityException $e) {
                $this->customLogger->error('Can\'t get attributes from product ID = ' . $product->getId());
            }
        }
        $customizableOptions = [];
        foreach ($configurable->getConfigurableOptions($product) as $attributeId => $configurableOption) {
            foreach ($configurableOption as $dataOption) {
                $options[$attributeId][$dataOption['value_index']] = $dataOption['option_title'];
                if (substr($dataOption['sku'], 23, 1) == '0') {
                    $classicStone = true;
                }
            }
        }
        foreach ($options as $attributeId => $option) {
            try {
                /** @var Attribute $attribute */
                $attribute = $this->productAttributeRepository->get($attributeId);
                if ($attribute->getData(AttributeInterface::FRONTEND_LABEL) == 'Center Stone Size') {
                    $product->setData('certified_stone', $classicStone);
                    continue;
                }
                foreach ($option as $index) {
                    $customizableOption = [
                        'title' => $index,
                        'price' => 0,
                        'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                        'sku' => $this->mappingSku[$attribute->getData(AttributeInterface::FRONTEND_LABEL)][$index],
                        'product_sku' => $sku,
                    ];
                    $customizableOptions[$attributeId][] = $customizableOption;
                }
            } catch (NoSuchEntityException $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        $bundleData = ['bundle' => $bundleOptions, 'options' => $customizableOptions];
        if (isset($links)) {
            $bundleData['links'] = $links;
        }
        return $bundleData;
    }

    /**
     * @param $productOptions
     * @param Product $product
     * @return false|string
     */
    protected function getTypeOfProduct($productOptions, $product)
    {
        $type = Type::TYPE_SIMPLE;
        if ($product->getSku() == 'LRENSL0091X') {
            return Type::TYPE_BUNDLE;
        }
        if (count($this->matchingBandHelper->getMatchingBands((int)$product->getId())) > 0) {
            return Type::TYPE_BUNDLE;
        }
        foreach ($productOptions as $productOption) {
            try {
                $productAttribute = $this->productAttributeRepository->get($productOption->getAttributeId());
                $label = $productAttribute->getData('frontend_label');

                if ($label == 'Center Stone Size') {
                    return Type::TYPE_BUNDLE;
                }
            } catch (NoSuchEntityException $e) {
                $this->_logger->critical($e->getMessage());
                return false;
            }
        }

        return $type;
    }

    /**
     * @param Product $product
     * @param array $data
     * @param Configurable $configurable
     * @deprecared
     * @return array
     */
    protected function reconfigurePrices(Product $product, array $data, Configurable $configurable)
    {
        $productPrices = [];
        $usedProducts = $configurable->getUsedProducts($product);
        $price = (float)0;
        /**
         * @var int $key
         * @var Product $config
         */
        if (count($usedProducts) > 0) {
            foreach ($usedProducts as $key => $config) {
                if ($price == 0) {
                    $price = $config->getPrice();
                }
                if ($price > $config->getPrice()) {
                    $price = (float)$config->getPrice();
                }
                $productPrices[$key] = $config->getPrice();
            }
            foreach ($data['options'] as $attributeId => &$options) {
                foreach ($options as $key => &$option) {
                    $option['price'] = $productPrices[$key] - $price;
                }
            }
        }
        return $data;
    }

    /**
     * @param Configurable\Attribute $option
     * @return array
     */
    private function getOption(Configurable\Attribute $option)
    {
        return [
            'title' => $option['label'],
            'default_title' => $option['label'],
            'type' => 'select',
            'required' => 1,
            'delete' => '',
            'price_type' => TierPriceInterface::PRICE_TYPE_FIXED
        ];
    }

    /**
     * @param Configurable\Attribute $productOption
     * @return array
     */
    private function prepareOptions(Configurable\Attribute $productOption)
    {
        $readyOptions = [];
        foreach ($productOption->getOptions() as $option) {
            $readyOptions[$option['value_index']] = $option['label'];
        }
        return $readyOptions;
    }

    /**
     * @param array $productIds
     * @param float $basePrice
     * @param string $originalSku
     * @return ProductLinkInterface[]
     * @throws NoSuchEntityException
     */
    private function prepareLinksForBundle(array $productIds, float $basePrice, string $originalSku)
    {
        $links = [];
        $uniqSkus = $this->getUniqSkus($productIds);
        foreach ($uniqSkus as $originalId => $sku) {
            $originalPrice = $this->productRepository->getById($originalId)->getPrice();
            $itemPrice = $originalPrice - $basePrice;
            $link = $this->linkHelper->createNewLink($sku, $itemPrice, $originalSku);
            if ($link == null) {
                $newSku = str_replace('CU', 'CR', $sku);
                $link = $this->linkHelper->createNewLink($newSku, $itemPrice, $originalSku);
            }
            if ($link == null) {
                if (substr($sku, 13, 4) == 'XXXX') {
                    $sku = substr_replace($sku, 'WHXX', 13, 4);
                    $link = $this->linkHelper->createNewLink($sku, $itemPrice, $originalSku);
                    if ($link == null) {
                        $newSku = str_replace('CU', 'CR', $sku);
                        $link = $this->linkHelper->createNewLink($newSku, $itemPrice, $originalSku);
                    }
                }
            }
            if ($link !== null) {
                $links[] = $link;
            } else {
                $this->customLogger->info('CAN\'T CREATE LINK FOR PRODUCT = ' . $originalSku);
            }
        }
        return $links;
    }

    /**
     * @param $productIds
     * @return array
     */
    private function getUniqSkus($productIds)
    {
        $skusForLikedProduct = [];
        foreach ($productIds as $id => $productId) {
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, true, 0, true);
                if ($product->isDisabled()) {
                    continue;
                }
                $sku = $product->getSku();
                $skusForLikedProduct[$id] = $this->productFunctionalHelper->getStoneSkuFromProductSku($sku);
                $this->productFunctionalHelper->addProductToDelete($product);
                if ($product->getData('bundle_sku') == null) {
                    $bundleSku = substr($sku, 11, 13);
                    $product->setData('bundle_sku', $bundleSku);
                    $product->setCustomAttribute('bundle_sku', $bundleSku);
                    $this->productRepository->save($product);
                }
            } catch (NoSuchEntityException $e) {
                $this->customLogger->info('SKU not found - ' . $e->getMessage());
            } catch (CouldNotSaveException $e) {
                $this->customLogger->info('Can\'t update bundle_sku - ' . $e->getMessage());
            } catch (InputException $e) {
                $this->customLogger->info('Can\'t update bundle_sku - ' . $e->getMessage());
            } catch (StateException $e) {
                $this->customLogger->info('Can\'t update bundle_sku - ' . $e->getMessage());
            }
        }
        return array_unique($skusForLikedProduct);
    }

    /**
     * @param $attribute
     * @param $index
     * @return mixed|string
     */
    private function getSkuForOption($attribute, $index)
    {
        return $this->mappingSku[$attribute][$index] ?? '';
    }
}
