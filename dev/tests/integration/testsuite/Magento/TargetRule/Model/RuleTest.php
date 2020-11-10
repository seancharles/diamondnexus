<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TargetRule\Model;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\TargetRule\Model\Rule
     */
    protected $_model;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(\Magento\TargetRule\Model\Rule::class);
    }

    /**
     * Test empty rules
     */
    public function testValidateDataOnEmpty()
    {
        $data = new \Magento\Framework\DataObject();
        $this->assertTrue($this->_model->validateData($data), 'True for empty object');
    }

    /**
     * Test valid rule
     */
    public function testValidateDataOnValid()
    {
        $data = new \Magento\Framework\DataObject();
        $data->setRule(
            ['actions' => ['test' => ['type' => \Magento\TargetRule\Model\Actions\Condition\Combine::class]]]
        );

        $this->assertTrue($this->_model->validateData($data), 'True for right data');
    }

    /**
     * Test invalid rule
     *
     * @param string $code
     * @dataProvider invalidCodesDataProvider
     */
    public function testValidateDataOnInvalidCode($code)
    {
        $data = new \Magento\Framework\DataObject();
        $data->setRule(
            [
                'actions' => [
                    'test' => [
                        'type' => \Magento\TargetRule\Model\Actions\Condition\Combine::class,
                        'attribute' => $code,
                    ],
                ],
            ]
        );
        $this->assertCount(1, $this->_model->validateData($data), 'Error for invalid attribute code');
    }

    /**
     * @return array
     */
    public static function invalidCodesDataProvider()
    {
        return [[''], ['_'], ['123'], ['!'], [str_repeat('2', 256)]];
    }

    /**
     * Test invalid rule type
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateDataOnInvalidType()
    {
        $data = new \Magento\Framework\DataObject();
        $data->setRule(['actions' => ['test' => ['type' => 'Magento\TargetRule\Invalid']]]);
        $this->_model->validateData($data);
    }

    /**
     * Test target rules with category rule conditions
     *
     * @param string $operator
     * @param int $categoryId
     * @param array $expectedProducts
     * @magentoDataFixture Magento/TargetRule/_files/products_with_attributes.php
     * @magentoDataFixture Magento/TargetRule/_files/related.php
     * @magentoAppIsolation enabled
     * @dataProvider categoryConditionDataProvider
     */
    public function testCategoryCondition(string $operator, int $categoryId, array $expectedProducts)
    {
        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\TargetRule\Model\Index $targetRuleIndexModel */
        $targetRuleIndexModel = $this->objectManager->create(\Magento\TargetRule\Model\Index::class);
        /** @var \Magento\TargetRule\Model\Rule $targetRuleModel */
        $targetRuleModel = $this->objectManager->create(\Magento\TargetRule\Model\Rule::class);

        $targetRuleModel->load('related', 'name');
        $data = [
            'actions' => [
                '1' => [
                    'type' => \Magento\TargetRule\Model\Actions\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                ],
                '1--1' => [
                    'type' => \Magento\TargetRule\Model\Actions\Condition\Product\Attributes::class,
                    'attribute' => 'category_ids',
                    'operator' => $operator,
                    'value' => $categoryId,
                    'is_value_processed' => false,
                    'value_type' => 'constant',
                ],
                '1--2' => [
                    'type' => \Magento\TargetRule\Model\Actions\Condition\Product\Attributes::class,
                    'attribute' => 'sku',
                    'operator' => '{}',
                    'value' => 'simple',
                    'is_value_processed' => false,
                    'value_type' => 'constant',
                ],
            ],
        ];
        $targetRuleModel->loadPost($data);
        $targetRuleModel->save();
        $targetRuleIndexModel->setType($targetRuleModel->getApplyTo());
        $targetRuleIndexModel->setProduct($productRepository->get('child_simple'));

        $actualProducts = [];
        foreach ($targetRuleIndexModel->getProductIds() as $sku) {
            $actualProducts[] = $productRepository->getById($sku)->getSku();
        }
        sort($expectedProducts);
        sort($actualProducts);
        $this->assertEquals($expectedProducts, $actualProducts);
    }

    /**
     * @return array
     */
    public function categoryConditionDataProvider(): array
    {
        return [
            'Product category does not contain 5 AND Product SKU contains "simple_product"' => [
                'operator' => '!{}',
                'categoryId' => 44,
                'expectedProducts' => [
                    'simple1',
                    'simple3',
                ],
            ],
            'Product category contains 5 AND Product SKU contains "simple_product"' => [
                'operator' => '{}',
                'categoryId' => 44,
                'expectedProducts' => [
                    'simple2',
                    'simple4',
                ],
            ],
        ];
    }
}
