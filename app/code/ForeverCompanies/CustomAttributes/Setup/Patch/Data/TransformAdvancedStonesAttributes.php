<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

class TransformAdvancedStonesAttributes implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetupFactory $eavSetupFactory;
    private AttributeRepositoryInterface $eavAttributeRepository;
    private CollectionFactory $productCollectionFactory;
    private Action $productActionObject;

    /**
     * List of all old attributes to be deleted
     * @var array|string[]
     */
    private array $oldAttributes = [
        'lab',
        'polish',
        'symmetry',
        'table_pct',
        'depth_pct',
        'length_to_width'
    ];

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param CollectionFactory $productCollectionFactory
     * @param Action $productActionObject
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $eavAttributeRepository,
        CollectionFactory $productCollectionFactory,
        Action $productActionObject
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productActionObject = $productActionObject;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     * @throws Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * create lab_report attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'lab_report');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'lab_report',
                [
                    'type' => 'int',
                    'label' => 'Lab',
                    'input' => 'select',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => [
                        'values' => [
                            'IGI',
                            'GIA',
                            'GCAL',
                            'Other',
                        ]
                    ]
                ]
            );

            // load the newly created attribute
            $labReportAttribute = $this->eavAttributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'lab_report'
            );

            // get the option labels and values of the new attribute
            $labReportOptions = [];
            $labReportAttributeOptions = $labReportAttribute->getSource()->getAllOptions(false);
            foreach ($labReportAttributeOptions as $option) {
                $labReportOptions[strtolower(trim($option['label']))] = $option['value'];
            }

            // create array of values and product ids based on old attribute
            $productLabs = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'lab']);
            foreach ($products->getItems() as $product) {
                $lab = $product->getData('lab');
                if (!is_null($lab) && $lab !== "") {
                    $productLabs[strtolower(trim($lab))][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($productLabs)) {
                foreach ($productLabs as $lab => $entityIds) {
                    $value = null;
                    if (array_key_exists(strtolower(trim($lab)), $labReportOptions)) {
                        $value = $labReportOptions[strtolower(trim($lab))];
                    } elseif ($lab !== "") {
                        $value = $labReportOptions['other'];
                    }
                    if (!is_null($value)) {
                        try {
                            $this->productActionObject->updateAttributes(
                                $entityIds,
                                ['lab_report' => $value],
                                0
                            );
                        } catch (Exception $e) {
                            echo "\nERROR - update lab_report attribute failed: " . $e->getMessage() . "\n";
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * create polish_grade attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'polish_grade');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'polish_grade',
                [
                    'type' => 'int',
                    'label' => 'Polish',
                    'input' => 'select',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => [
                        'values' => [
                            'Good',
                            'Very Good',
                            'Excellent'
                        ]
                    ]
                ]
            );

            // load the newly created attribute
            $polishGradeAttribute = $this->eavAttributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'polish_grade'
            );

            // get the option labels and values of the new attribute
            $polishGradeOptions = [];
            $polishGradeAttributeOptions = $polishGradeAttribute->getSource()->getAllOptions(false);
            foreach ($polishGradeAttributeOptions as $option) {
                $polishGradeOptions[strtolower(trim($option['label']))] = $option['value'];
            }

            // create array of values and product ids based on old attribute
            $polishGrades = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'polish']);
            foreach ($products->getItems() as $product) {
                $polish = $product->getData('polish');
                if (!is_null($polish) && $polish !== "") {
                    $polishGrades[strtolower(trim($polish))][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($polishGrades)) {
                foreach ($polishGrades as $polish => $entityIds) {
                    $value = null;
                    if (array_key_exists(strtolower(trim($polish)), $polishGradeOptions)) {
                        $value = $polishGradeOptions[strtolower(trim($polish))];
                    }
                    if (!is_null($value)) {
                        try {
                            $this->productActionObject->updateAttributes(
                                $entityIds,
                                ['polish_grade' => $value],
                                0
                            );
                        } catch (Exception $e) {
                            echo "\nERROR - update polish_grade attribute failed: " . $e->getMessage() . "\n";
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * create symmetry_grade attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'symmetry_grade');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'symmetry_grade',
                [
                    'type' => 'int',
                    'label' => 'Symmetry',
                    'input' => 'select',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => [
                        'values' => [
                            'Good',
                            'Very Good',
                            'Excellent'
                        ]
                    ]
                ]
            );

            // load the newly created attribute
            $symmetryGradeAttribute = $this->eavAttributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'symmetry_grade'
            );

            // get the option labels and values of the new attribute
            $symmetryGradeOptions = [];
            $symmetryGradeAttributeOptions = $symmetryGradeAttribute->getSource()->getAllOptions(false);
            foreach ($symmetryGradeAttributeOptions as $option) {
                $symmetryGradeOptions[strtolower(trim($option['label']))] = $option['value'];
            }

            // create array of values and product ids based on old attribute
            $symmetryGrades = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'symmetry']);
            foreach ($products->getItems() as $product) {
                $symmetry = $product->getData('symmetry');
                if (!is_null($symmetry) && $symmetry !== "") {
                    $symmetryGrades[strtolower(trim($symmetry))][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($symmetryGrades)) {
                foreach ($symmetryGrades as $symmetry => $entityIds) {
                    $value = null;
                    if (array_key_exists(strtolower(trim($symmetry)), $symmetryGradeOptions)) {
                        $value = $symmetryGradeOptions[strtolower(trim($symmetry))];
                    }
                    if (!is_null($value)) {
                        try {
                            $this->productActionObject->updateAttributes(
                                $entityIds,
                                ['symmetry_grade' => $value],
                                0
                            );
                        } catch (Exception $e) {
                            echo "\nERROR - update symmetry_grade attribute failed: " . $e->getMessage() . "\n";
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * create table_percent attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'table_percent');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'table_percent',
                [
                    'type' => 'decimal',
                    'label' => 'Table %',
                    'input' => 'price',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );

            // create array of values and product ids based on old attribute
            $tablePctVals = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'table_pct']);
            foreach ($products->getItems() as $product) {
                $tablePct = $product->getData('table_pct');
                if (!is_null($tablePct) && $tablePct !== "") {
                    $tablePctVals[$tablePct][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($tablePctVals)) {
                foreach ($tablePctVals as $tablePct => $entityIds) {
                    try {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['table_percent' => $tablePct],
                            0
                        );
                    } catch (Exception $e) {
                        echo "\nERROR - update table_percent attribute failed: " . $e->getMessage() . "\n";
                        return false;
                    }
                }
            }
        }

        /**
         * create depth_percent attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'depth_percent');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'depth_percent',
                [
                    'type' => 'decimal',
                    'label' => 'Depth %',
                    'input' => 'price',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );

            // create array of values and product ids based on old attribute
            $depthPctVals = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'depth_pct']);
            foreach ($products->getItems() as $product) {
                $depthPct = $product->getData('depth_pct');
                if (!is_null($depthPct) && $depthPct !== "") {
                    $depthPctVals[$depthPct][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($depthPctVals)) {
                foreach ($depthPctVals as $depthPct => $entityIds) {
                    try {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['depth_percent' => $depthPct],
                            0
                        );
                    } catch (Exception $e) {
                        echo "\nERROR - update depth_percent attribute failed: " . $e->getMessage() . "\n";
                        return false;
                    }
                }
            }
        }

        /**
         * create length_width_ratio attribute
         */
        $attribute = $eavSetup->getAttribute(Product::ENTITY, 'length_width_ratio');
        if (!$attribute) {
            // create the attribute
            $eavSetup->addAttribute(
                Product::ENTITY,
                'length_width_ratio',
                [
                    'type' => 'decimal',
                    'label' => 'L:W Ratio',
                    'input' => 'price',
                    'source' => '',
                    'frontend' => '',
                    'required' => false,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 1,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'General',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );

            // create array of values and product ids based on old attribute
            $lwRatioVals = [];
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect(['entity_id', 'length_to_width']);
            foreach ($products->getItems() as $product) {
                $lwRatio = $product->getData('length_to_width');
                if (!is_null($lwRatio) && $lwRatio !== "") {
                    $lwRatioVals[$lwRatio][] = (int)$product->getData('entity_id');
                }
            }
            unset($products);

            // update the new attribute's values
            if (!empty($lwRatioVals)) {
                foreach ($lwRatioVals as $lwRatio => $entityIds) {
                    try {
                        $this->productActionObject->updateAttributes(
                            $entityIds,
                            ['length_width_ratio' => $lwRatio],
                            0
                        );
                    } catch (Exception $e) {
                        echo "\nERROR - update length_width_ratio attribute failed: " . $e->getMessage() . "\n";
                        return false;
                    }
                }
            }
        }

        // end setup
        $this->moduleDataSetup->getConnection()->endSetup();

        // remove all the old attributes
        $this->removeOldAttributes();

        return true;
    }

    /**
     * Remove all the old attributes
     */
    private function removeOldAttributes()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->oldAttributes as $attributeCode) {
            $attribute = $eavSetup->getAttribute(Product::ENTITY, $attributeCode);
            if ($attribute) {
                $eavSetup->removeAttribute(
                    Product::ENTITY,
                    $attributeCode
                );
            }
        }
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
