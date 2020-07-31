<?php

namespace ForeverCompanies\CustomAttributes\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /** @var EavSetupFactory */
    protected $eavSetupFactory;

    protected $standardFieldList = [
        'price',
        'special_price',
        'special_from_date',
        'special_to_date',
        'minimal_price',
        'cost',
        'tier_price',
        'weight',
        'affirm_product_mfp',
        'affirm_product_mfp_type',
        'affirm_product_mfp_priority',
        'affirm_product_mfp_start_date',
        'affirm_product_mfp_end_date',
        'affirm_product_promo_id',
        'bestseller',
        'carat_weight',
        'cert_url_key',
        'clarity',
        'color',
        'cost',
        'cross_sell_reference_id',
        'custom_price',
        'cut_grade',
        'default_category',
        'feed_age_group',
        'feed_brand',
        'feed_category',
        'feed_condition',
        'feed_description',
        'feed_expiration_date',
        'feed_gender',
        'feed_image',
        'feed_title',
        'feed_type',
        'feed_url',
        'high_margin',
        'in_product_feed',
        'in_sitemap',
        'shape',
        'shipping_status',
    ];

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '0.0.2', '<')) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


            // make these attributes applicable to new product type - Bracelet
            $fieldList = $this->standardFieldList + [
                    'certified_stone',
                    'cut_type',
                    'gemstone',
                    //'metal_type',
                ];
            foreach ($fieldList as $field) {
                //var_dump($field);
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Bracelet::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Bracelet::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Chain
            $fieldList = $this->standardFieldList + [
                    'certified_stone',
                    'chain_length',
                    'chain_size',
                    'cut_type',
                    'matching_band',
                    //'metal_type',
                    'stone_abbrev'
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Chain::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Chain::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Earring
            $fieldList = $this->standardFieldList + [
                    'certified_stone',
                    'cut_type',
                    //'metal_type',
                    'stone_abbrev'
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Earring::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Earring::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Diamond
            $fieldList = $this->standardFieldList + [
                    'featured',
                    'supplier',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Diamond::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Diamond::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Stone
            $fieldList = $this->standardFieldList + [
                    'certified_stone',
                    'cut_type',
                    'featured',
                    'gemstone',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Stone::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Stone::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Matched
            $fieldList = $this->standardFieldList + [
                    'display_price_range',
                    'cut_type',
                    //'metal_type',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Matched::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Matched::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Band
            $fieldList = $this->standardFieldList + [
                    'band_width',
                    'certified_stone',
                    'certified_stone_copy',
                    'cut_type',
                    'gemstone',
                    'ring_size',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Band::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Band::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Necklace
            $fieldList = $this->standardFieldList + [
                    'band_width',
                    'certified_stone',
                    'certified_stone_copy',
                    'cut_type',
                    'gemstone',
                    'ring_size',
                    'stone_abbrev',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Necklace::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Necklace::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Pendant
            $fieldList = $this->standardFieldList + [
                    'band_width',
                    'certified_stone',
                    'certified_stone_copy',
                    'gemstone',
                    //'metal_type'
                    'ring_size',
                    'stone_abbrev',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Pendant::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Pendant::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Ring
            $fieldList = $this->standardFieldList + [
                    'band_width',
                    'certified_stone',
                    'certified_stone_copy',
                    'cut_type',
                    'engraving',
                    'gemstone',
                    //'metal_type'
                    'ring_size',
                    'stone_abbrev',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Ring::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Ring::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Ring setting
            $fieldList = $this->standardFieldList + [
                    'certified_stone_copy',
                    'cut_type',
                    'gemstone',
                    //'metal_type'
                    'ring_size',
                    'stone_abbrev',
                    'stone_quality'
                ];

            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Ringsetting::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Ringsetting::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            // make these attributes applicable to new product type - Watch
            $fieldList = $this->standardFieldList + [
                    'band_width',
                    'certified_stone',
                    'cut_type',
                    'gemstone',
                    //'metal_type'
                    'ring_size',
                    'stone_abbrev',
                ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(\ForeverCompanies\CustomAttributes\Model\Product\Type\Watch::TYPE_ID, $applyTo)) {
                    $applyTo[] = \ForeverCompanies\CustomAttributes\Model\Product\Type\Watch::TYPE_ID;
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

        }

        $setup->endSetup();
    }
}
