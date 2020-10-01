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

class RevertEavAttributes implements DataPatchInterface
{

    protected $filteringAttributes = [
        'chain_length',
        'chain_size',
        'metal_type',
        'ring_size',
        'certified_stone',
        'color',
        'cut_grade',
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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->filteringAttributes as $filteringAttribute) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $filteringAttribute);
            if ($attribute) {
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'backend_type',
                    'int'
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'frontend_input',
                    'select'
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'backend_model',
                    null
                );
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $filteringAttribute,
                    'source_model',
                    \Magento\Eav\Model\Entity\Attribute\Source\Table::class
                );
            }
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'color',
                'backend_model',
                ArrayBackend::class
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'shape',
                'backend_model',
                ArrayBackend::class
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'color',
                'source_model',
                null
            );
            $eavSetup->updateAttribute(
                Product::ENTITY,
                'shape',
                'source_model',
                null
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
        return [

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
