<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class ProductType extends AbstractHelper
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attrSetRepository;

    /**
     * @var AbstractSource
     */
    protected $eavConfig;

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
     * ProductType constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param Logger $logger
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Config $eavConfig
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Config $eavConfig
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->attrSetRepository = $attributeSetRepository;
        try {
            $this->eavConfig = $eavConfig->getAttribute(Product::ENTITY, 'product_type')->getSource();
        } catch (LocalizedException $e) {
            $this->logger->error('Can\t get product_type attribute source - ' . $e->getMessage());
        }
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     */
    public function setProductType(Product $product)
    {
        if ($product->getData('product_type') == null) {
            $attributeSetName = $this->attrSetRepository->get($product->getAttributeSetId())->getAttributeSetName();
            $productType = $this->attributeSetToProductType($attributeSetName);
            $product->setCustomAttribute('product_type', $productType);
            if ($product->getCustomAttribute('product_type') == 'Stone') {
                $product->setCustomAttribute('allow_in_bundles', 1);
            }
        }
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    protected function attributeSetToProductType(string $attributeSetName)
    {
        $typeName = $this->mappingProductType[$attributeSetName];
        return $this->eavConfig->getOptionId($typeName);
    }
}
