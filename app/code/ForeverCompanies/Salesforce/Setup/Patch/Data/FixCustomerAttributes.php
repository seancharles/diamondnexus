<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup\Patch\Data;

use Magento\CatalogStaging\Model\ResourceModel\ProductSequence;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\Customer;

class FixCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
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
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceConnection $resourceConnection
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resourceConnection,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceConnection = $resourceConnection;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customAttribute = $this->eavConfig->getAttribute('customer', 'sf_acctid');

        if ($customAttribute) {
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'backend_type',
                'varchar'
            );
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'attribute_set_id',
                $attributeSetId
            );
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'attribute_group_id',
                $attributeGroupId
            );
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'sort_order',
                150
            );
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'frontend_input',
                'text'
            );
            $eavSetup->updateAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'sf_acctid',
                'frontend_label',
                'Salesforce Account Id'
            );
            $tableName = $this->resourceConnection->getTableName('eav_entity_attribute');
            $this->resourceConnection->getConnection()->insertOnDuplicate(
                $tableName,
                [
                    'entity_type_id' => '1',
                    'attribute_set_id' => '1',
                    'attribute_group_id' => '1',
                    'attribute_id' => $customAttribute->getId(),
                    'sort_order' => 10000
                ]
            );
            $tableName = $this->resourceConnection->getTableName('customer_form_attribute');
            $this->resourceConnection->getConnection()->insertOnDuplicate(
                $tableName,
                [
                    'form_code' => 'adminhtml_customer',
                    'attribute_id' => $customAttribute->getId(),
                ]
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
