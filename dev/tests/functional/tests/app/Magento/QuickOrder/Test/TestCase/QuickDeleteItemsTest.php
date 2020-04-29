<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;

/**
 * Test quick delete items
 *
 * Test Flow:
 * 1. Open quick order page.
 * 3. Fill form.
 * 3. Remove first product.
 * 4. Perform asserts.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-67963
 */
class QuickDeleteItemsTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /** @var QuickOrderPage */
    protected $quickOrderPage;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Perform needed injections
     *
     * @param QuickOrderPage $quickOrderPage
     */
    public function __inject(QuickOrderPage $quickOrderPage)
    {
        $this->quickOrderPage = $quickOrderPage;
    }

    /**
     * Test quick delete items
     *
     * @param array $productsList
     * @param string $configData
     * @return array
     */
    public function test($productsList, $configData = null)
    {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->createProducts($productsList);

        $this->quickOrderPage->open();
        $this->quickOrderPage->getItems()->fill($products);
        $this->quickOrderPage->getItems()->removeFirstItem();

        return ['products' => $products];
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    protected function createProducts($products)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
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
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
