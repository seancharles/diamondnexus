<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;

/**
 * Preconditions:
 * 1. Create shared catalog with assigned company.
 * 2. Create products assigned to category.
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Assign products to the shared catalog.
 * 3. Creating duplicate shared catalog.
 * 4. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68544
 */
class DuplicateSharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var SharedCatalogCreate $sharedCatalogCreate
     */
    protected $sharedCatalogCreate;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCreate = $sharedCatalogCreate;
    }

    /**
     * Assign products to shared catalog and test their visibility.
     *
     * @param SharedCatalog $sharedCatalog
     * @param Category $category
     * @param array $products
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, Category $category, array $products, $configData = null)
    {
        // Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();
        $category->persist();
        $productsToAssign = $this->prepareProducts($category, $products);

        // Test steps
        $this->stepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $sharedCatalog, 'products' => $productsToAssign]
        )->run();

        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->openEdit($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $this->sharedCatalogCreate->getFormPageActions()->duplicate();
        $this->sharedCatalogCreate->getFormPageActions()->save();
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdateCategoryPermissions');

        return [
            'sharedCatalog' => $sharedCatalog,
            'category' => $category,
            'products' => $productsToAssign
        ];
    }

    /**
     * Create products.
     *
     * @param Category $category
     * @param array $products
     * @return array
     */
    private function prepareProducts(Category $category, array $products)
    {
        $assignProducts = [];
        foreach ($products as $product) {
            $productDataSet = explode('::', $product['dataset']);
            $productFixture = $this->fixtureFactory->createByCode(
                $productDataSet[0],
                [
                    'dataset' => $productDataSet[1],
                    'data' => ['category_ids' => ['dataset' => null, 'category' => $category]],
                ]
            );
            $productFixture->persist();
            $assignProducts[] = $productFixture;
        }

        return $assignProducts;
    }

    /**
     * Roll back configuration settings.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
