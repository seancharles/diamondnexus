<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\Link;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;


class Mapping extends AbstractHelper
{

    /**
     * @var string[]
     */
    protected $mappingProductType = [
        'Migration_Bracelets' => 'Bracelet',
        'Migration_Chains' => 'Chain',
        'Migration_Custom Cut' => 'Other',
        'Migration_Default' => 'Other',
        'Migration_Earrings' => 'Earring',
        'Migration_Gift Card' => 'Gift Card',
        'Migration_Loose Diamonds' => 'Diamond',
        'Migration_Loose Stones' => 'Stone',
        'Migration_Matched Sets' => 'Matched Set',
        'Migration_Matching Bands' => 'Matching Band',
        'Migration_Mens Rings' => 'Ring',
        'Migration_Necklaces' => 'Necklace',
        'Migration_Pendants' => 'Pendant',
        'Migration_Pure Carbon Rings' => 'Ring',
        'Migration_Ring Settings' => 'Ring Setting',
        'Migration_Rings' => 'Ring',
        'Migration_Simple' => 'Other',
        'Migration_Watches' => 'Watch'
    ];
    /**
     * @var array
     */
    protected $mappingSku = [
        'Precious Metal' => [
            '10K White Gold' => '10WX',
            '10K Yellow Gold' => '10YX',
            '14K Yellow Gold & Platinum Plating' => '14DX',
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
            'Lorian Platinum' => 'LPXX',
            'Lorian Platinum & Yellow Gold' => 'LPYG',
            'Palladium' => 'PDXX',
            'Platinum' => 'PTXX',
            'Silver & 14K Rose Gold	' => 'SSRX',
            'Sterling Silver' => 'SSXX',
            'Silver & 14K Yellow Gold' => 'SSYX',
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
            'Black' => 'BLXX',
            'Blue Topaz' => 'BUTZ',
            'CocoBollo & Damascus' => 'CBDM',
            'CocoBollo & Titanium' => 'CBTX',
            'Charcoal & Titanium' => 'CCTX',
            'Chocolate Multi' => 'CHML',
            'Chocolate & White	' => 'CHWH',
            'Chocolate' => 'CHXX',
            'Champagne & Chocolate' => 'CPCH',
            'Champagne Multi' => 'CPML',
            'Champagne & White' => 'CPWH',
            'Champagne' => 'CPXX',
            'Cross Satin Black' => 'CRSB',
            'Cross Satin Silver' => 'CRSS',
            'Cross Satin' => 'CRSX',
            'Emerald & White' => 'EMWH',
            'Emerald' => 'EMXX',
            'Fiji Orangewood & Black Zirconium' => 'FOBZ',
            'Glacial Ice & White' => 'GIWH',
            'Glacial Ice' => 'GIXX',
            'Hammer' => 'HMXX',
            'Meteorite' => 'MEXX',
            'Multi Topaz' => 'MLTZ',
            'Multi Color' => 'MLXX',
            'New Canary Multi' => 'NCML',
            'Canary & Sapphire' => 'NCSP',
            'Canary & White' => 'NCWH',
            'Canary' => 'NCXX',
            'Pink Topaz' => 'PKTZ',
            'Red Topaz' => 'RDTZ',
            'Rose & White' => 'RSWH',
            'Rose' => 'RSXX',
            'Ruby & White' => 'RUWH',
            'Ruby' => 'RUXX',
            'Rosewood & Titanium' => 'RXTX',
            'Sapphire & Canary' => 'SPNC',
            'Sapphire & White' => 'SPWH',
            'Sapphire' => 'SPXX',
            'White & Black' => 'WHBL',
            'White & Chocolate' => 'WHCH',
            'White & Champagne' => 'WHCP',
            'White & Emerald' => 'WHEM',
            'White & Glacial Ice' => 'WHGI',
            'White Multi' => 'WHML',
            'White & New Canary' => 'WHNC',
            'White Pearl' => 'WHPR',
            'Whit & Rose' => 'WHRS',
            'White & Ruby' => 'WHRU',
            'White & Sapphire' => 'WHSP',
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
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var ProductFunctional
     */
    protected $productFunctionalHelper;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ProductRepositoryInterface $productRepository
     * @param LinkInterfaceFactory $linkFactory
     * @param ProductFunctional $productFunctionalHelper
     * @param Config $eavConfig
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ProductRepositoryInterface $productRepository,
        LinkInterfaceFactory $linkFactory,
        ProductFunctional $productFunctionalHelper,
        Config $eavConfig
    )
    {
        parent::__construct($context);
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productRepository = $productRepository;
        $this->linkFactory = $linkFactory;
        $this->productFunctionalHelper = $productFunctionalHelper;
        $this->eavConfig = $eavConfig->getAttribute(Product::ENTITY, 'product_type')->getSource();
    }

    /**
     * @param array $options
     * @param string $title
     * @return false|mixed|string|null
     */
    public function getAttributeIdFromProductOptions(array $options, string $title)
    {
        /** @var Configurable\Attribute $option */
        foreach ($options as $option) {
            if ($option->getLabel() == $title) {
                return $option->getAttributeId();
            }
        }
        return false;
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    public function attributeSetToProductType(string $attributeSetName)
    {
        $typeName = $this->mappingProductType[$attributeSetName];
        return $this->eavConfig->getOptionId($typeName);
    }

    /**
     * @param Product $product
     * @param array $productOptions
     * @return array[]
     */
    public function prepareOptionsForBundle(Product $product, array $productOptions)
    {
        $bundleOptions = [];
        $options = [];
        foreach ($productOptions as $productOption) {
            if ($productOption['label'] != 'Carat Weight') {
                $bundleOptions[] = [
                    'title' => $productOption['label'],
                    'default_title' => $productOption['label'],
                    'type' => 'select',
                    'required' => 1,
                    'delete' => '',
                    'price_type' => TierPriceInterface::PRICE_TYPE_FIXED
                ];
                $options[$productOption['attribute_id']] = $this->prepareOptions($productOption);
                if ($productOption['label'] == 'Precious Metal') {
                    $product->setData('metal_type', null);
                }
                if ($productOption['label'] == 'Certified Stone') {
                    $product->setData('certified_stone', null);
                }

            } else {
                /** @var ProductExtension $extensionAttributes */
                $extensionAttributes = $product->getExtensionAttributes();
                $configurableProductLinks = $extensionAttributes->getConfigurableProductLinks();
                $links = $this->prepareLinksForBundle($configurableProductLinks);
                $product->setData('carat_weight', null);
            }
        }
        $customizableOptions = [];
        /** @var Configurable $configurable */
        $configurable = $product->getTypeInstance();
        foreach ($configurable->getConfigurableOptions($product) as $attributeId => $configurableOption) {
            foreach ($configurableOption as $dataOption) {
                $options[$attributeId][$dataOption['value_index']] = $dataOption['option_title'];
            }

        }
        foreach ($options as $attributeId => $option) {
            try {
                /** @var Attribute $attribute */
                $attribute = $this->productAttributeRepository->get($attributeId);
                if ($attribute->getData(AttributeInterface::FRONTEND_LABEL) == 'Center Stone Size') {
                    continue;
                }
                foreach ($option as $index) {
                    $customizableOption = [
                        'title' => $index,
                        'price' => 0,
                        'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                        'sku' => $this->mappingSku[$attribute->getData(AttributeInterface::FRONTEND_LABEL)][$index],
                        'product_sku' => $product->getSku(),
                    ];
                    $customizableOptions[$attributeId][] = $customizableOption;
                }
            } catch (NoSuchEntityException $e) {
                /** TODO: Exception */
            }
        }
        $bundleData = ['bundle' => $bundleOptions, 'options' => $customizableOptions];
        if (isset($links)) {
            $bundleData['links'] = $links;
        }

        return $bundleData;
    }

    /**
     * @param Configurable\Attribute $productOption
     * @return array
     */
    protected function prepareOptions(Configurable\Attribute $productOption)
    {
        $readyOptions = [];
        foreach ($productOption->getValues() as $value) {
            $readyOptions[$value->getValueIndex()] = '';
        }
        return $readyOptions;
    }

    /**
     * @param array $productIds
     * @return ProductLinkInterface[]
     */
    protected function prepareLinksForBundle(array $productIds)
    {
        $links = [];
        $uniqSkus = $this->getUniqSkus($productIds);
        foreach ($uniqSkus as $sku) {
            try {
                $product = $this->productRepository->get($sku);
                if ($product->getId() !== null) {
                    $links[] = $this->createNewLink($product);
                }
            } catch (NoSuchEntityException $e) {
                /** TODO: Exception */
            }
        }

        return $links;
    }

    /**
     * @param ProductInterface $product
     * @return LinkInterface
     */
    private function createNewLink(ProductInterface $product)
    {
        /** @var Link $link */
        $link = $this->linkFactory->create();
        $link->setSku($product->getSku());
        $link->setData('name', $product->getName());
        $link->setData('selection_qty', 1);
        $link->setData('product_id', $product->getId());
        $link->setIsDefault(false);
        $link->setData('selection_price_value', $product->getPrice());
        $link->setData('selection_price_type', LinkInterface::PRICE_TYPE_FIXED);
        return $link;
    }

    /**
     * @param $productIds
     * @return array
     */
    private function getUniqSkus($productIds)
    {
        $skusForLikedProduct = [];
        foreach ($productIds as $productId) {
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, true, 0, true);
                $this->productFunctionalHelper->addProductToDelete($product);
                $sku = $product->getSku();
                $skusForLikedProduct[] = $this->productFunctionalHelper->getStoneSkuFromProductSku($sku);
            } catch (NoSuchEntityException $e) {
                /** TODO: Exception */
            }
        }
        return array_unique($skusForLikedProduct);
    }

}
