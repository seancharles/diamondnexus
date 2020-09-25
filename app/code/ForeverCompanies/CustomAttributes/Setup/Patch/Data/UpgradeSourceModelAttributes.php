<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CertifiedStone;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainLength;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\ChainSize;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Color;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\CutType;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\MetalType;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\RingSize;
use ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source\Shape;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpgradeSourceModelAttributes implements DataPatchInterface, PatchRevertableInterface
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
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->previousSetup = $addIsTransformedProductAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * 'chain_length',
         * 'chain_size',
         * 'metal_type',
         * 'ring_size',
         * 'certified_stone',
         * 'color',
         * 'cut',
         * 'shape'
         */
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_length',
            'source_model',
            ChainLength::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'chain_size',
            'source_model',
            ChainSize::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'metal_type',
            'source_model',
            MetalType::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'ring_size',
            'source_model',
            RingSize::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'certified_stone',
            'source_model',
            CertifiedStone::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'color',
            'source_model',
            Color::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'cut_type',
            'source_model',
            CutType::class
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'shape',
            'source_model',
            Shape::class
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        /** TODO! */
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
            UpgradeFilteringProductAttributes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '0.0.5';
    }
}
