<?php

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

/**
 * Class AddSortAttributes
 * @package ForeverCompanies\CustomAttributes\Setup\Patch\Data
 */
class AddSortAttributes implements DataPatchInterface
{

    /**
     * @var string[][]
     */
    protected $attributes = [
        [
            'code' => 'clarity_sort',
            'title' => 'Clarity Sort',
            'sort_order' => '11',
        ],
        [
            'code' => 'color_sort',
            'title' => 'Color Sort',
            'sort_order' => '21',
        ],
        [
            'code' => 'cut_grade_sort',
            'title' => 'Cut Grade Sort',
            'sort_order' => '51',
        ],
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
     * AddSortAttributes constructor.
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
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->attributes as $attribute) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attribute['code'],
                    [
                        'type' => 'int',
                        'label' => $attribute['title'],
                        'input' => 'text',
                        'required' => false,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'used_in_product_listing' => true,
                        'default' => null,
                        'visible' => true,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => false,
                        'visible_on_front' => true,
                        'unique' => false,
                        'sort_order' => $attribute['sort_order'],
                        'visible_in_advanced_search' => true,
                        'used_for_sort_by' => true,
                    ]
                );
                $eavSetup->addAttributeToGroup(
                    Product::ENTITY,
                    'Migration_Loose Diamonds',
                    'General', // group
                    $attribute['code'],
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [RemoveEavAndUpdateLooseDiamonds::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
