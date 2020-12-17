<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\Customer;

class FixCustomerAttributes implements DataPatchInterface
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
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create();

        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
		
		$customAttribute = $this->eavConfig->getAttribute('customer', 'sf_acctid');
		
		if($customAttribute) {
			
			$customAttribute->addData([
				'attribute_set_id' => $attributeSetId,
				'attribute_group_id' => $attributeGroupId,
				'sort_order' => 110
			]);
			$customAttribute->save();
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
