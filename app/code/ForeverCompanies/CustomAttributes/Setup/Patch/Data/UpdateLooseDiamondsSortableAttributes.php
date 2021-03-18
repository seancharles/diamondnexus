<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpdateLooseDiamondsSortableAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * list of attributes to update
     */
    private $attributes = [
        'carat_weight',
        'shape'
    ];

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
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->attributes as $attribute) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'is_searchable',
                1
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'used_for_sort_by',
                1
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'is_visible_in_advanced_search',
                1
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->attributes as $attribute) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'is_searchable',
                0
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'used_for_sort_by',
                0
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'is_visible_in_advanced_search',
                0
            );
        }

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
        return [];
    }
}
