<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Rules\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;


/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
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
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(
        ModuleDataSetupInterface  $setup,
        ModuleContextInterface  $context
    ) {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->upgradeCustomAttributes();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function upgradeCustomAttributes()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_grade',
            'is_used_for_promo_rules',
            true
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'is_used_for_promo_rules',
            true
        );
    }
}
