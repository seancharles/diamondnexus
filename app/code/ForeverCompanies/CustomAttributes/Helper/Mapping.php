<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;


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
        // TODO: Map for get SKU
    ];

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * Mapping constructor.
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $productAttributeRepository
    )
    {
        parent::__construct($context);
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    public function attributeSetToProductType(string $attributeSetName)
    {
        return $this->mappingProductType[$attributeSetName];
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
            if ($productOption['label'] != 'Center Stone Size') {
                $bundleOptions[] = [
                    'title' => $productOption['label'],
                    'default_title' => $productOption['label'],
                    'type' => 'select',
                    'required' => 1,
                    'delete' => '',
                ];
                $options[$productOption['attribute_id']] = $this->prepareOptions($productOption);

            } else {
                /** TODO LOGIC FOR STONE */
            }
        }
        $customizableOptions = [];
        /** @var Configurable $configurable */
        $configurable = $product->getTypeInstance();
        foreach ($configurable->getConfigurableOptions($product) as $attributeId => $configurableOption) {
            foreach ($configurableOption as $dataOption) {
                $options[$attributeId] = $dataOption['option_title'];
            }

        }
        foreach($options as $attributeId => $option) {
            try {
                /** @var Attribute $attribute */
                $attribute = $this->productAttributeRepository->get($attributeId);
                $customizableOption = [
                    'title' => $option,
                    'price' => 0, //TODO: NEED CHANGE!!!
                    'price_type' => 'select',
                    'sku' => $this->mappingSku[$attribute->getData(AttributeInterface::FRONTEND_LABEL)][$option],
                ];
                $customizableOptions[$attributeId] = $customizableOption;
            } catch (NoSuchEntityException $e) {
                /** TODO: Exception */
            }
        }
        return ['bundle' => $bundleOptions, 'options' => $customizableOptions];
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $productOption
     * @return array
     */
    protected function prepareOptions(\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $productOption)
    {
        $readyOptions = [];
        foreach ($productOption->getValues() as $value)
        {
            $readyOptions[$value->getValueIndex()] = '';
        }
        return $readyOptions;
    }

}
