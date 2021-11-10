<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

class UpdateAdvancedStonesAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    private array $attributes = [
        'cvd_hpht' => [
            'label' => 'CVD/HPHT',
            'type' => 'int',
            'input' => 'select',
            'sort_order' => 20,
            'filterable' => 1,
            'option' => [
                'CVD',
                'HPHT'
            ]
        ],
        'hearts_and_arrows' => [
            'label' => 'Hearts & Arrows',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 30,
            'filterable' => 1,
            'option' => []
        ],
        'as_grown' => [
            'label' => 'As Grown/Treated',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 40,
            'filterable' => 1,
            'option' => []
        ],
        'grown_in_usa' => [
            'label' => 'Grown in the USA',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 50,
            'filterable' => 1,
            'option' => []
        ],
        'made_in_usa' => [
            'label' => 'Made in the USA',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 60,
            'filterable' => 1,
            'option' => []
        ],
        'carbon_neutral' => [
            'label' => 'Carbon Neutral',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 40,
            'filterable' => 1,
            'option' => []
        ],
        'certified_sustainable' => [
            'label' => 'Certified Sustainable',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 70,
            'filterable' => 1,
            'option' => []
        ],
        'next_day_ship' => [
            'label' => 'Next Day Ship',
            'type' => 'int',
            'input' => 'boolean',
            'sort_order' => 80,
            'filterable' => 1,
            'option' => []
        ]
    ];

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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // build list of all attributes we need to remove
        $attributesToDelete = [];
        foreach ($this->attributes as $code => $data) {
            $attributesToDelete[] = $code;
        }
        $attributesToDelete = array_merge($attributesToDelete, ['hpht', 'cvd', 'hpht_cvd']);

        // remove all attributes
        foreach ($attributesToDelete as $attributeCode) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);
            if ($attribute) {
                $eavSetup->removeAttribute(
                    Product::ENTITY,
                    $attributeCode
                );
            }
        }

        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // loop through all attributes and add them if they don't exist
        foreach ($this->attributes as $attributeCode => $attributeData) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);
            if (!$attribute) {
                // build out the attribute properties
                $attr = [
                    'type' => $attributeData['type'],
                    'label' => $attributeData['label'],
                    'input' => $attributeData['input'],
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => $attributeData['sort_order'],
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => $attributeData['filterable'],
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false
                ];

                // if the attribute has options for a select field, add them in
                if (array_key_exists('option', $attributeData) && !empty($attributeData['option'])) {
                    $attr['option'] = [
                        'values' => $attributeData['option']
                    ];
                }

                // add the attribute
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    $attr
                );
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [
            RemoveEavAndUpdateLooseDiamonds::class
        ];
    }

    /**
     * {}
     */
    public static function getVersion(): string
    {
        return '0.0.1';
    }
}
