<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Zend_Validate_Exception;

class AddStoneImportPriceOverrideProductAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'stone_import_price_override');
        if ($attribute) {
            $eavSetup->removeAttribute(
                Product::ENTITY,
                'stone_import_price_override'
            );
        }
        $eavSetup->addAttribute(
            Product::ENTITY,
            'stone_import_price_override',
            [
                'type' => 'int',
                'label' => 'Stone Import Price Override',
                'input' => 'select',
                'source' => '',
                'frontend' => '',
                'required' => true,
                'backend' => '',
                'sort_order' => '50',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE]),
                'group' => 'General',
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'special_from_date',
            'visible',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'special_from_date',
            'apply_to',
            implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'special_to_date',
            'visible',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'special_to_date',
            'apply_to',
            implode(',', [Type::TYPE_SIMPLE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE])
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'stone_import_price_override');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
