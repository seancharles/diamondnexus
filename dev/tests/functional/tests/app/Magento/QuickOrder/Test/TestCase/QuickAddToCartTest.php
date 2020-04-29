<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Cli\EnvWhitelist;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;

/**
 * Test quick add to cart
 *
 * Test Flow:
 * 1. Open quick order page.
 * 3. Fill form.
 * 3. Add products to cart.
 * 4. Perform asserts.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-67940
 */
class QuickAddToCartTest extends Injectable
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
     * DomainWhitelist CLI
     *
     * @var EnvWhitelist
     */
    private $envWhitelist;

    /**
     * Perform needed injections
     *
     * @param QuickOrderPage $quickOrderPage
     * @param EnvWhitelist $envWhitelist
     */
    public function __inject(
        QuickOrderPage $quickOrderPage,
        EnvWhitelist $envWhitelist
    ) {
        $this->quickOrderPage = $quickOrderPage;
        $this->envWhitelist = $envWhitelist;
    }

    /**
     * Test quick add to cart
     *
     * @param array $productsList
     * @param string $configData
     * @return array
     */
    public function test($productsList, $configData = null)
    {
        $this->envWhitelist->addHost('example.com');
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->createProducts($productsList);

        $this->quickOrderPage->open();
        $this->quickOrderPage->getItems()->fill($products);
        $this->quickOrderPage->getActions()->clickAddToCart();

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
        $this->envWhitelist->removeHost('example.com');
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
