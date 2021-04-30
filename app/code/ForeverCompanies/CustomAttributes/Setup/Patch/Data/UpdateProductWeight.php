<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateProductWeight implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Action $action
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productActionObject = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToFilter(array(array('attribute'=>'type_id','in' => [
            'simple',
            'configurable',
            'bundle'
        ])));
        $productCollection->setPageSize(1000);
        
        $count = $productCollection->getSize();
        $pages = ceil($count / 1000);

        for($i=1; $i<$pages; $i++) {
            // temp storage for products mass update ids
            $productIds = [];
            
            $productCollection->setCurPage($i);
            
            foreach ($productCollection as $product) {
                $productIds[] = $product->getId();
            }
            
            $this->productActionObject->updateAttributes(
                $productIds,
                ['weight' => 1],
                0
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
