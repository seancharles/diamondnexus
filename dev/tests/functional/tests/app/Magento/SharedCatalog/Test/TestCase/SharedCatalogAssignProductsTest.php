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

/**
 * Preconditions:
 * 1. Create shared catalog with assigned company.
 * 2. Create products assigned to different categories.
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Assign products to the shared catalog.
 * 3. Login to the SF as a company admin.
 * 4. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68554
 */
class SharedCatalogAssignProductsTest extends Injectable
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
     * Inject pages.
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
     * Assign products to shared catalog and test their visibility.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $categories
     * @param array $products
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, array $categories, array $products, $configData = null)
    {
        // Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();
        $customer = $sharedCatalog->getDataFieldConfig('companies')['source']->getCompanies()[0]
            ->getDataFieldConfig('customer')['source']->getCustomer();
        $categoriesStructure = $this->prepareCategories($categories);
        $productsToAssign = $this->prepareProducts($categoriesStructure, $products);
        $data = $this->prepareData($categoriesStructure, $productsToAssign['all_products'], $products);

        // Test steps
        $this->stepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $sharedCatalog, 'products' => $productsToAssign['products_to_assign']]
        )->run();
        $this->stepFactory->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        return [
            'categoryProducts' => $data,
            'categoriesStructure' => $categoriesStructure
        ];
    }

    /**
     * Create categories.
     *
     * @param array $categories
     * @param Category|null $parentCategory [optional]
     * @return array
     */
    private function prepareCategories(array $categories, Category $parentCategory = null)
    {
        $data = [];
        foreach ($categories as $key => $categoryData) {
            $category = $this->fixtureFactory->createByCode(
                'category',
                [
                    'dataset' => $categoryData['dataset'],
                    'data' => $parentCategory ? ['parent_id' => ['dataset' => '-', 'source' => $parentCategory]] : [],
                ]
            );
            $category->persist();
            $data[$key] = [
                'category' => $category,
                'product_count' => $categoryData['product_count']
            ];
            if (!empty($categoryData['children'])) {
                $data = array_merge(
                    $data,
                    $this->prepareCategories(
                        $categoryData['children'],
                        $category
                    )
                );
            }
        }
        return $data;
    }

    /**
     * Create products.
     *
     * @param array $categories
     * @param array $products
     * @return array
     */
    private function prepareProducts(array $categories, array $products)
    {
        $productsToAssign = [];
        $allProducts = [];
        foreach ($products as $key => $product) {
            $categoriesIndexes = explode(',', $key);
            $productCategories = [];
            foreach ($categoriesIndexes as $categoryIndex) {
                $productCategories[] = $categories[$categoryIndex]['category'];
            }
            $productDataSet = explode('::', $product['dataset']);
            $productFixture = $this->fixtureFactory->createByCode(
                $productDataSet[0],
                [
                    'dataset' => $productDataSet[1],
                    'data' => [
                        'category_ids' => $productCategories
                    ],
                ]
            );
            if ($product['assign_to_catalog']) {
                $productsToAssign[$key] = $productFixture;
            }
            $allProducts[$key] = $productFixture;
            $productFixture->persist();
        }

        return [
            'products_to_assign' => $productsToAssign,
            'all_products' => $allProducts
        ];
    }

    /**
     * Prepare data for assertions.
     *
     * @param array $categories
     * @param array $products
     * @param array $productsData
     * @return array
     */
    private function prepareData(array $categories, array $products, array $productsData)
    {
        $data = [];
        foreach ($categories as $index => $category) {
            foreach ($products as $key => $product) {
                if (in_array($index, explode(',', $key))) {
                    $data[] = [
                        'category' => $category['category'],
                        'products' => $product,
                        'visible' => $productsData[$key]['assign_to_catalog']
                    ];
                }
            }
        }
        return $data;
    }

    /**
     * Log out customer and roll back configuration settings.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->stepFactory->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
