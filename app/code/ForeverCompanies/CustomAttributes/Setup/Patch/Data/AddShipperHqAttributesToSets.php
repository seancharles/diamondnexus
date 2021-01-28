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
    
    private $attributeList = [
        419 => 'shipperhq_availability_date',
        410 => 'shipperhq_declared_value',
        412 => 'shipperhq_dim_group',
        408 => 'shipperhq_handling_fee',
        406 => 'shipperhq_hs_code',
        417 => 'shipperhq_malleable_product',
        418 => 'shipperhq_master_boxes',
        423 => 'shipperhq_nmfc_class',
        425 => 'shipperhq_nmfc_sub',
        416 => 'shipperhq_poss_boxes',
        407 => 'shipperhq_shipping_fee',
        404 => 'shipperhq_shipping_group',
        409 => 'shipperhq_volume_weight',
        405 => 'shipperhq_warehouse'
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
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
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
                $eavSetup->addAttributeGroup (
                    $entityTypeId,
                    $attributeSetId,
                    "Shipping",
                    999
                );
                
                // get the group id
                $group_id = $eavSetup->getAttributeGroupId(
                    $entityTypeId,
                    $attributeSetId,
                    "Shipping"
                );
                
                foreach($this->attributeList as $attributeId => $attributeCode) {
                    $eavSetup->addAttributeToSet(
                        $entityTypeId,
                        $attributeSetId,
                        $group_id,
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
