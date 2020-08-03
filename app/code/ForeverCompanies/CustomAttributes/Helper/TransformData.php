<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend_Db_Select;

class TransformData extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Config
     */
    protected $eav;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attrSetRepository;

    /**
     * @var string[]
     */
    private $mappingProductType = [
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
     * @param Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository
    )
    {
        parent::__construct($context);
        $this->eav = $config;
        $this->productCollectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->attrSetRepository = $attributeSetRepository;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getProductsForTransformCollection()
    {
        $isTransformedAttr = $this->eav->getAttribute(Product::ENTITY, 'is_transformed');
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id')
            ->joinLeft(
                ['eav_int' => 'catalog_product_entity_int'],
                "`eav_int`.`row_id` = `e`.`row_id` AND
            `eav_int`.`attribute_id` = {$isTransformedAttr->getAttributeId()} AND
            `eav_int`.`store_id` = 0",
                ['value']
            )->where('eav_int.value is null');
        return $productCollection;
    }

    /**
     * @param int $entityId
     * @throws NoSuchEntityException
     */
    public function transformProduct(int $entityId)
    {
        $product = $this->productRepository->getById($entityId);
        $attributeSetName = $this->attrSetRepository->get($product->getAttributeSetId())->getAttributeSetName();
        $product->setCustomAttribute('product_type', $this->attributeSetToProductType($attributeSetName));
        // TO BE CONTINUED...
        exit;
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    protected function attributeSetToProductType(string $attributeSetName)
    {
        return $this->mappingProductType[$attributeSetName];
    }

}
