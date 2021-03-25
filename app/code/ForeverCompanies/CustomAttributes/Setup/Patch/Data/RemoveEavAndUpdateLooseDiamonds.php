<?php

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

/**
 * Class AddSwatchImageAttribute
 */
class RemoveEavAndUpdateLooseDiamonds implements DataPatchInterface
{
    /**
     * @var string[]
     */
    protected $removeEav = [
        'miusa',
        'lot',
        'rapaport_price',
        'price_der_carat',
        'height',
        'memo_status',
        'inscription_num',
        'location'
    ];

    /**
     * @var string[][]
     */
    protected $attributes = [
        [
            'code' => 'import_id',
            'title' => 'Import ID',
            'type' => 'int',
            'input' => 'text'
        ],
        [
            'code' => 'as_grown',
            'title' => 'As Grown',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'born_on_date',
            'title' => 'Born On Date',
            'type' => 'datetime',
            'input' => 'date'
        ],
        [
            'code' => 'carbon_neutral',
            'title' => 'Carbon Neutral',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'charitable_contribution',
            'title' => 'Charitable Contribution',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'color_of_colored_diamonds',
            'title' => 'Color of COlored Diamonds',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'custom',
            'title' => 'Custom',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'cvd',
            'title' => 'CVD',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'depth_mm',
            'title' => 'Depth mm',
            'type' => 'decimal',
            'input' => 'text'
        ],
        [
            'code' => 'hpht',
            'title' => 'HPHT',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'hue',
            'title' => 'Hue',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'intensity',
            'title' => 'Intensity',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'length_to_width',
            'title' => 'Length to Width',
            'type' => 'decimal',
            'input' => 'text'
        ],
        [
            'code' => 'measurements',
            'title' => 'Measurements',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'patented',
            'title' => 'Patented',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'polish',
            'title' => 'Polish',
            'type' => 'varchar',
            'input' => 'text'
        ],
        [
            'code' => 'rapaport',
            'title' => 'Rapaport',
            'type' => 'int',
            'input' => 'text'
        ],
        [
            'code' => 'last_seen_date',
            'title' => 'Last Seen Date',
            'type' => 'datetime',
            'input' => 'date'
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
     * AddSwatchImageAttribute constructor.
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
        foreach ($this->removeEav as $removeEav) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $removeEav);
            if ($attribute) {
                $eavSetup->removeAttribute(
                    Product::ENTITY,
                    $removeEav
                );
            }
        }
        foreach ($this->attributes as $attribute) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    $attribute['code'],
                    [
                        'type' => $attribute['type'],
                        'label' => $attribute['title'],
                        'input' => $attribute['input'],
                        'required' => false,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'used_in_product_listing' => true,
                        'default' => null,
                        'visible' => true,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => true,
                        'unique' => false,
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
        return [AddDiamondImporterAttributes::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
