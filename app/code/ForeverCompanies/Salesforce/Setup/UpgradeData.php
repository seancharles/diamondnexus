<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;

use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

use Magento\Customer\Model\Customer;
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

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function upgrade(
        ModuleDataSetupInterface  $setup,
        ModuleContextInterface  $context
    ) {
        $setup->startSetup();
        
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->createCustomerAttribute($setup);
        }
        
        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'sf_orderid',
                [
                    'type'               => 'varchar',
                    'visible'            => true,
                    'required'           => false,
                    'user_defined'       => true,
                    'label'              => 'Salesforce Order ID',
                    'system'             => false,
                    'visible_on_front'   => false,
                    'is_used_in_grid'    => true,
                    'is_visible_in_grid' => true,
                ]
            );
            
            // remove invalid attribute
            $eavSetup->removeAttribute(Order::ENTITY,'sf_order_itemid');

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lastsync_at',
                [
                    'type'               => 'varchar',
                    'label'              => 'Last Sync',
                    'input'              => 'text',
                    'visible'            => true,
                    'required'           => false,
                    'user_defined'       => true,
                    'system'             => false,
                    'visible_on_front'   => false,
                    'is_used_in_grid'    => true,
                    'is_visible_in_grid' => true,
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'sf_order_itemid',
                [
                    'type'               => 'varchar',
                    'label'              => 'Salesforce Order Line Item ID',
                    'input'              => 'text',
                    'visible'            => true,
                    'required'           => false,
                    'user_defined'       => true,
                    'system'             => false,
                    'visible_on_front'   => false,
                    'is_used_in_grid'    => true,
                    'is_visible_in_grid' => true,
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            
            $attributeSetId = $eavSetup->getDefaultAttributeSetId('catalog_product');
            
            $eavSetup->addAttributeToSet(
                'catalog_product',
                $attributeSetId,
                'General',
                'sf_orderid'
            );

            $eavSetup->addAttributeToSet(
                'catalog_product',
                $attributeSetId,
                'General',
                'lastsync_at'
            );
            
            $eavSetup->addAttributeToSet(
                'catalog_product',
                $attributeSetId,
                'General',
                'sf_order_itemid'
            );
            
            $attributeOptions = [
                'type'     => 'varchar',
                'visible'  => true,
                'required' => false
            ];
            
            $salesSetup->addAttribute('order', 'sf_orderid', $attributeOptions);
            $salesSetup->addAttribute('order', 'lastsync_at', $attributeOptions);
            $salesSetup->addAttribute('order_item', 'sf_order_itemid', $attributeOptions);
            $salesSetup->addAttribute('order_item', 'lastsync_at', $attributeOptions);
        }
        
        $setup->endSetup();
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