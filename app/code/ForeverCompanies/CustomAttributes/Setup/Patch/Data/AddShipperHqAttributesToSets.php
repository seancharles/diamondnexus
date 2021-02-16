<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class AddShipperHqAttributesToSets implements DataPatchInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    
    private $dimensionalAttributeList = [
		'shipperhq_dim_group',
		'ship_separately',
		'ship_length',
		'ship_width',
		'ship_height',
		'shipperhq_poss_boxes'
    ];
	
	private $freightAttributeList = [
		'freight_class',
		'must_ship_freight'
	];
	
	private $shippingAttributeList = [
		'shipperhq_shipping_group',
		'shipperhq_warehouse',
		'shipperhq_hs_code'
	];

    /**
     * Constructor
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
		Attribute $eavAttribute
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
		$this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        
        foreach ($attributeSetIds as $attributeSetId) {
            if ($attributeSetId) {
				
				// dimensional group
                $eavSetup->addAttributeGroup (
                    $entityTypeId,
                    $attributeSetId,
                    "Dimensional Shipping",
                    997
                );
				
                $dim_group_id = $eavSetup->getAttributeGroupId(
                    $entityTypeId,
                    $attributeSetId,
                    "Dimensional Shipping"
                );
				
                foreach($this->dimensionalAttributeList as $attributeCode) {
					$attributeId = $this->eavAttribute->getIdByCode(
						\Magento\Catalog\Model\Product::ENTITY,
						$attributeCode
					);
					
                    $eavSetup->addAttributeToSet(
                        $entityTypeId,
                        $attributeSetId,
                        $dim_group_id,
                        $attributeId,
                        999
                    );
                }
				
				// freight group
                $eavSetup->addAttributeGroup (
                    $entityTypeId,
                    $attributeSetId,
                    "Freight Shipping",
                    998
                );
				
                $freight_group_id = $eavSetup->getAttributeGroupId(
                    $entityTypeId,
                    $attributeSetId,
                    "Freight Shipping"
                );
				
                foreach($this->freightAttributeList as $attributeCode) {
					$attributeId = $this->eavAttribute->getIdByCode(
						\Magento\Catalog\Model\Product::ENTITY,
						$attributeCode
					);
					
                    $eavSetup->addAttributeToSet(
                        $entityTypeId,
                        $attributeSetId,
                        $freight_group_id,
                        $attributeId,
                        999
                    );
                }
				
				// shipping group
                $eavSetup->addAttributeGroup (
                    $entityTypeId,
                    $attributeSetId,
                    "Shipping",
                    999
                );
                
                $shipping_group_id = $eavSetup->getAttributeGroupId(
                    $entityTypeId,
                    $attributeSetId,
                    "Shipping"
                );
                
                foreach($this->shippingAttributeList as $attributeCode) {
					$attributeId = $this->eavAttribute->getIdByCode(
						\Magento\Catalog\Model\Product::ENTITY,
						$attributeCode
					);
					
                    $eavSetup->addAttributeToSet(
                        $entityTypeId,
                        $attributeSetId,
                        $shipping_group_id,
                        $attributeId,
                        999
                    );
                }
            }
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
