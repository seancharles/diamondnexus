<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;

/**
 * Preconditions:
 * 1. Create Shared Catalog.
 * 2. Create category.
 * 3. Create product.
 *
 * Steps:
 * 1. Open Admin.
 * 2. Open shared catalog.
 * 3. Add product to the shared catalog.
 * 4. Save shared catalog.
 * 5. Perform assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68650
 */
class AddProductsToTheSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
{
    /**
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $stepFactory;

    /**
     * @var string
     */
    private $configData;

    /**
     * Inject pages.
     *
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
    }

    /**
     * Add products to the shared catalog and check that category is categories tree.
     *
     * @param SharedCatalog $sharedCatalog
     * @param Category $category
     * @param array $productsList
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        Category $category,
        array $productsList,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();
        $category->persist();
        $product = $this->prepareProduct($category, $productsList[0]);
        $this->openSharedCatalogConfiguration($sharedCatalog);
        $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
        $this->openSharedCatalogConfiguration($sharedCatalog);

        return [
            'catalogProduct' => $product
        ];
    }

    /**
     * Create product.
     *
     * @param Category $category
     * @param string $product
     * @return CatalogProductSimple
     */
    private function prepareProduct(Category $category, $product)
    {
        $productDataSet = explode('::', $product);
        /**
         * @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $productFixture
         */
        $productFixture = $this->fixtureFactory->createByCode(
            $productDataSet[0],
            [
                'dataset' => $productDataSet[1],
                'data' => ['category_ids' => ['dataset' => null, 'category' => $category]],
            ]
        );
        $productFixture->persist();

        return $productFixture;
    }

    /**
     * Open shared catalog configuration page.
     *
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    private function openSharedCatalogConfiguration(SharedCatalog $sharedCatalog)
    {
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
    }
}
