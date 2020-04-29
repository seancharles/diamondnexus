<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create 2 simple products.
 *
 * Steps:
 * 1. Open quick order page.
 * 2. Enter first letters of product name.
 * 3. Perform assertion.
 * 4. Hover first suggested product with mouse pointer, click on it and enter quantity.
 * 5. Perform assertion.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-68605
 *
 */
class QuickOrderTest extends Injectable
{
    /**
     * QuickOrder page.
     *
     * @var QuickOrderPage
     */
    private $quickOrder;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject dependencies.
     *
     * @param QuickOrderPage $quickOrder
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(QuickOrderPage $quickOrder, TestStepFactory $stepFactory)
    {
        $this->quickOrder = $quickOrder;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Search product by product name on QuickOrder page.
     *
     * @param array $products
     * @param string $configData
     * @return void
     */
    public function test(array $products, $configData)
    {
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $simpleProducts = $this->createProducts($products);
        $this->quickOrder->open();
        $itemBlock = $this->quickOrder->getItems()->getItemBlock();
        $itemBlock->setSku($simpleProducts[0]->getName());
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    private function createProducts(array $products)
    {
        $data = [
            ['name' => 'name' . time(), 'sku' => 'sku' . time()],
            ['name' => 'name' . time(), 'sku' => 'sku' . time()]
        ];
        $createProductsStep = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products, 'data' => $data]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
