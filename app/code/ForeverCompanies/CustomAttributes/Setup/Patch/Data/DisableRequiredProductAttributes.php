<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

/**
 * Add new custom layout related attributes.
 */
class DisableRequiredProductAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $eavSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'stone_cut',
            'is_required',
            false
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_stone_min',
            'is_required',
            false
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_stone_max',
            'is_required',
            false
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'anticipated_ship_date',
            'is_required',
            false
        );
    }

    public static function getVersion()
    {
        return '0.0.3';
    }
}
