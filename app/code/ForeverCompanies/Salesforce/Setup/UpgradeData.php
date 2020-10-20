<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Sales\Model\Order;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface  $setup,
        ModuleContextInterface  $context)
    {
        $setup->startSetup();
        $this->createCustomerAttribute($setup);
        $this->createOrdersAttribute($setup);
        $setup->endSetup();
    }

    private function createOrdersAttribute($setup)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $salesSetup->addAttribute(
            Order::ENTITY,
            'sf_orderid',
            [
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'label' => 'Salesforce Order ID',
                'system' => false,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
            ]
        );

        $salesSetup->addAttribute(
            Order::ENTITY,
            'sf_order_itemid',
            [
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'label' => 'Salesforce Order Line Item ID',
                'system' => false,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
            ]
        );
    }

    private function createCustomerAttribute($setup)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'sf_acctid',
            [
                'type' => 'text',
                'label' => 'Salesforce Account Id',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' =>999,
                'system' => 0,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,

            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'sf_acctid')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer','customer_address_edit'],
            ]);

        $attribute->save();

    }
}
