<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Zend_Validate_Exception;

class AddDiamondImporterAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var array[]
     */
    protected $attributes = [
        [
            'code' => 'lot',
            'title' => 'Lot #',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'lab',
            'title' => 'Lab',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'polish',
            'title' => 'Polish',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'symmetry',
            'title' => 'Symmetry',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'fluor',
            'title' => 'Fluor',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'rapaport_price',
            'title' => 'Lab',
            'required' => false,
            'type' => 'decimal',
            'input' => 'price'
        ],
        [
            'code' => 'pct_off_rap',
            'title' => '% Off Rap',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'price_per_carat',
            'title' => 'Price/Ct',
            'required' => false,
            'type' => 'decimal',
            'input' => 'price'
        ],
        [
            'code' => 'cert_num',
            'title' => 'Certificate #',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'length',
            'title' => 'Length',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'width',
            'title' => 'Width',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'height',
            'title' => 'Height',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'depth_pct',
            'title' => 'Depth %',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'table_pct',
            'title' => 'Table %',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'girdle',
            'title' => 'Girdle',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'culet',
            'title' => 'Culet',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'origin',
            'title' => 'Origin',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'memo_status',
            'title' => 'Memo Status',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'inscription_num',
            'title' => 'Inscription #',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'diamond_img_url',
            'title' => 'Diamond Image',
            'required' => false,
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'stone_import_custom_cost',
            'title' => 'Custom Cost',
            'required' => false,
            'type' => 'decimal',
            'input' => 'price'
        ],
        [
            'code' => 'stone_import_custom_price',
            'title' => 'Custom Price',
            'required' => false,
            'type' => 'decimal',
            'input' => 'price'
        ],
        [
            'code' => 'stone_import_cost_override',
            'title' => 'Custom Price',
            'required' => false,
            'type' => 'int',
            'input' => 'boolean'
        ]
    ];

    /**
     * @var string[]
     */
    protected $attributesToGroup = [
        'shape',
        'color',
        'clarity',
        'weight',
        'cut_grade',
        'carat_weight',
        'supplier',
        'description',
        'location',
        'cert_url_key',
        'video_url',
        'online'
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
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->attributes as $data) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $data['code']);
            if ($attribute) {
                $eavSetup->removeAttribute(
                    Product::ENTITY,
                    $data['code']
                );
            }
            $eavSetup->addAttribute(
                Product::ENTITY,
                $data['code'],
                [
                    'type' => $data['type'],
                    'label' => $data['title'],
                    'input' => $data['input'],
                    'source' => '',
                    'frontend' => '',
                    'required' => $data['required'],
                    'backend' => '',
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'unique' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                'Migration_Loose Diamonds',
                'General', // group
                $data['code'],
            );
        }
        foreach ($this->attributesToGroup as $data) {
            $eavSetup->addAttributeToGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                'Migration_Loose Diamonds',
                'General', // group
                $data,
            );
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->attributes as $data) {
            $eavSetup->removeAttribute(Product::ENTITY, $data['code']);
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

    public static function getVersion()
    {
        return '0.0.3';
    }
}
