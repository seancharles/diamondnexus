<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Test quick add to cart with custom shared catalog.
 *
 * Preconditions:
 * 1. Enable B2B features: Company, Quick Order and Shared Catalog.
 * 2. Create 2 products.
 * 3. Add the first product to public shared catalog.
 * 4. Create a custom shared catalog with assigned company.
 * 5. Add the both created products to the new shared catalog.
 *
 * Steps:
 * 1. Login as admin of the company, that assigned to created shared catalog.
 * 2. Open Quick Order page.
 * 3. Search the second product by SKU.
 * 4. Add found product to the shopping cart.
 * 5. Perform assertions.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-68677
 */
class QuickAddToCartWithSharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Perform bin/magento commands for reindex indexers.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Perform bin/magento commands for cache clean.
     *
     * @var Cache
     */
    private $cache;

    /**
     * @var QuickOrderPage
     */
    private $quickOrderPage;

    /**
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * Prepare test data.
     *
     * @param Indexer $indexer
     * @param Cache $cache
     * @return void
     */
    public function __prepare(Indexer $indexer, Cache $cache)
    {
        $this->indexer = $indexer;
        $this->cache = $cache;
    }

    /**
     * Perform needed injections.
     *
     * @param QuickOrderPage $quickOrderPage
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        QuickOrderPage $quickOrderPage,
        TestStepFactory $testStepFactory
    ) {
        $this->quickOrderPage = $quickOrderPage;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Test quick add to cart with custom shared catalog.
     *
     * @param array $productsList
     * @param SharedCatalog $publicSharedCatalog
     * @param SharedCatalog $customSharedCatalog
     * @param string $configData [optional]
     * @return array
     */
    public function test(
        array $productsList,
        SharedCatalog $publicSharedCatalog,
        SharedCatalog $customSharedCatalog,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->testStepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productsList]
        )->run()['products'];
        $this->testStepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $publicSharedCatalog, 'products' => [$products[0]]]
        )->run();
        $customSharedCatalog->persist();
        $this->testStepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $customSharedCatalog, 'products' => $products]
        )->run();
        $this->reindexAndCleanCache();

        //Steps
        $companyAdmin = $customSharedCatalog->getDataFieldConfig('companies')['source']->getCompanies()[0]
            ->getDataFieldConfig('customer')['source']->getCustomer();
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $companyAdmin]
        )->run();
        $this->quickOrderPage->open();
        $itemBlock = $this->quickOrderPage->getItems()->getItemBlock(1);
        $itemBlock->setSku($products[1]->getSku(), false);
        $this->quickOrderPage->getItems()->focusOutFromInput();
        $itemBlock->waitResultVisible();
        $this->quickOrderPage->getActions()->clickAddToCart();

        return ['products' => [$products[1]]];
    }

    /**
     * Perform reindex and flush cache operations
     *
     * @return void
     */
    private function reindexAndCleanCache()
    {
        $this->indexer->reindex();
        $this->cache->flush();
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
