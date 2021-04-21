<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpdateAttributeLabelsAndScopes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    
    private $labelChangeArr;
    private $scopeChangeArr;
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
        
        $this->labelChangeArr = array(
            "color_of_colored_diamonds" => "Color of Colored Diamonds",
            "depth_mm" => "Depth (MM)",
            "stone_import_cost_override" => "Custom Price Override"
        );
        
        $this->scopeChangeArr = array(
            "bundle_price_use",
            "bundle_sku",
            "bundle_tags",
            "stone_cut",
            "stone_carat",
            "allowed_in_settings",
            "stone_tags",
            "anticipated_ship_date",
            "ring_stone_min",
            "ring_stone_max",
            "acw",
            "stone_import_price_override",
            "dev_tag",
            "is_media_transformed"
        );
        
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->labelChangeArr as $k => $v) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $k,
                'frontend_label',
                $v
             );
        }
        
        foreach ($this->scopeChangeArr as $scopeAttr) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $scopeAttr,
                'is_global',
                ScopedAttributeInterface::SCOPE_GLOBAL
            );
        }
        
        $eavSetup->removeAttribute(
            Product::ENTITY,
            'price_per_carat'
        );
        
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($this->scopeChangeArr as $scopeAttr) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $scopeAttr,
                'is_global',
                ScopedAttributeInterface::SCOPE_STORE
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
    
    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '0.0.9';
    }
}
