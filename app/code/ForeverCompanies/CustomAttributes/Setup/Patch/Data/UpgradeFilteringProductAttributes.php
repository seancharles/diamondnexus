<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpgradeFilteringProductAttributes implements DataPatchInterface, PatchRevertableInterface
{

    protected $filteringAttributes = [
        'chain_length',
        'chain_size',
        'metal_type',
        'ring_size',
        'certified_stone',
        'color',
        'cut',
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
     * @var AddIsTransformedProductAttribute
     */
    private $previousSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AddIsTransformedProductAttribute $addIsTransformedProductAttribute
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AddIsTransformedProductAttribute $addIsTransformedProductAttribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->previousSetup = $addIsTransformedProductAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->filteringAttributes as $filteringAttribute) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $filteringAttribute);
            if ($attribute) {
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'backend_type',
                    'varchar'
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'frontend_input',
                    'multiselect'
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'backend_model',
                    ArrayBackend::class
                );
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(
            Product::ENTITY,
            'is_transformed'
        );
        $this->previousSetup->apply();
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

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '0.0.4';
    }
}
