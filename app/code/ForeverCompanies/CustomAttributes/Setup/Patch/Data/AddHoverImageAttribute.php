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
class AddHoverImageAttribute implements DataPatchInterface
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
     * @return AddHoverImageAttribute|void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'hover_image');
        if ($attribute) {
            $eavSetup->removeAttribute(
                Product::ENTITY,
                'hover_image'
            );
        }

        $eavSetup->addAttribute(
            Product::ENTITY,
            'hover_image',
            [
                'type' => 'varchar',
                'label' => 'Hover',
                'input' => 'media_image',
                'frontend' => Image::class,
                'required' => false,
                'sort_order' => 3,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true
            ]
        );
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
    public function getAliases()
    {
        return [];
    }
}
