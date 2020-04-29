<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Indexer\Test\TestStep\ReindexStep;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Enable Company and SharedCatalog B2B features
 *
 * Steps:
 * 1. Open Admin.
 * 2. Create a shared catalog.
 * 3. Create new company and assign it to created SC.
 * 4. Open Product Creation page.
 * 5. Fill required data and add new category from this page.
 * 6. Click "Save" button.
 * 7. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-80322
 */
class CreateCategoryFromProductPageTest extends Injectable
{
    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject entities.
     *
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Run create product simple entity test.
     *
     * @param Category $category
     * @param CatalogProductSimple $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param SharedCatalog $sharedCatalog
     * @param bool $customerLogIn
     * @param string|null $configData
     * @return array
     */
    public function test(
        Category $category,
        CatalogProductSimple $product,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        SharedCatalog $sharedCatalog,
        ReindexStep $reindexStep,
        $customerLogIn,
        $configData = null
    ): array {
        $this->configData = $configData;
        // Steps
        $sharedCatalog->persist();
        /** TODO: REMOVE 'reindex step' AFTER FIX MC-21717 */
        $reindexStep->run();
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $newProductPage->getProductForm()->fill($product, null, $category);
        $newProductPage->getFormPageActions()->save();
        $categories[] = $this->fixtureFactory->createByCode(
            'category',
            [
                'data' => [
                    'name' => $category->getData('name'),
                    'url_key' => strtolower($category->getData('name')),
                    'parent_id' => $category->getData('parent_id'),
                    'category_products' => [
                        'products' => [$product]
                    ]
                ]
            ]
        );
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => true]
        )->run();
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdateCategoryPermissions');

        if ($customerLogIn) {
            $customer = $sharedCatalog->getDataFieldConfig('companies')['source']->getCompanies()[0]
                ->getDataFieldConfig('customer')['source']->getCustomer();
            $this->stepFactory->create(
                \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                ['customer' => $customer]
            )->run();
        }

        return ['product' => $product, 'categories' => $categories];
    }

    /**
     * Clean data after test run.
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
