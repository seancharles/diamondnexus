<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Customer\Test\Fixture\Customer;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Util\Command\Cli\Indexer;

/**
 * Preconditions:
 * 1. Create categories and products.
 * 2. Create a shared catalog.
 * 3. Create a company with customer assigned to the company.
 * 4. Assign shared catalog to the company.
 *
 * Steps:
 * 1. Configure categories and products for the shared catalog.
 * 2. Login on the storefront.
 * 3. Perform assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68543, @ZephyrId MAGETWO-68543
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureSharedCatalogCategoriesTest extends AbstractSharedCatalogConfigurationTest
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Indexer.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Page AdminCache.
     *
     * @var AdminCache
     */
    private $adminCache;

    /**
     * Inject pages.
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @param ObjectManager $objectManager
     * @param Indexer $indexer
     * @param AdminCache $adminCache
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory,
        ObjectManager $objectManager,
        Indexer $indexer,
        AdminCache $adminCache
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
        $this->objectManager = $objectManager;
        $this->indexer = $indexer;
        $this->adminCache = $adminCache;
    }

    /**
     * Configure SharedCatalog.
     *
     * @param Customer $customer
     * @param SharedCatalog $sharedCatalog
     * @param array $catalogData
     * @param bool $configureCatalog [optional]
     * @param string|null $configData [optional]
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function test(
        Customer $customer,
        SharedCatalog $sharedCatalog,
        array $catalogData,
        $configureCatalog = true,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $sharedCatalog->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => ['email' => $customer->getEmail()],
            ]
        );
        $company->persist();
        $this->stepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\AssignCompanyStep::class,
            ['sharedCatalog' => $sharedCatalog, 'company' => $company]
        )->run();
        $catalogStructure = $this->prepareCatalog($catalogData);

        //Steps
        $lastCategoryName = '';
        if ($configureCatalog) {
            $this->sharedCatalogIndex->open();
            $this->openConfiguration($sharedCatalog->getName());
            $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
            $categoriesTree = $this->sharedCatalogConfigure->getStructureJstree();
            $categoriesTree->setTreeType('structure')->expandAll();
            $categoriesTree->toggleNode('Root Catalog');
            foreach ($catalogStructure as $data) {
                if (!$data['isCategorySelected']) {
                    $categoriesTree->toggleNode($data['category']->getName());
                }
                if (!$data['isProductsSelected']) {
                    $categoriesTree->findTreeNode($data['category']->getName())->click();
                    foreach ($data['products'] as $product) {
                        $this->sharedCatalogConfigure->getStructureGrid()
                            ->uncheckSwitcherItem(['sku' => $product->getSku()]);
                        $this->sharedCatalogConfigure->getStructureGrid()->waitForLoader();
                    }
                }
                if (!empty($data['isLast'])) {
                    $lastCategoryName = $data['category']->getName();
                }
            }
            $this->sharedCatalogConfigure->getNavigation()->nextStep();
            $this->sharedCatalogConfigure->getNavigation()->nextStep();
            $this->sharedCatalogConfigure->getPageActionBlock()->save();
        }
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdateCategoryPermissions');
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');
        $this->cleanCache();
        $this->indexer->reindex();
        $this->stepFactory->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();

        return [
            'categoryLevels' => $this->getCategoryLevels($catalogStructure),
            'lastCategoryName' => $lastCategoryName,
            'categoriesAvailability' => array_map(
                function ($categoryData) {
                    return [
                        'available' => $categoryData['isCategorySelected'],
                        'category' => $categoryData['category'],
                    ];
                },
                $catalogStructure
            ),
            'categoryProducts' => array_map(
                function ($categoryData) {
                    return [
                        'visible' => $categoryData['isProductsSelected'],
                        'category' => $categoryData['category'],
                        'products' => $categoryData['products'],
                    ];
                },
                array_filter(
                    $catalogStructure,
                    function ($categoryData) {
                        return $categoryData['isCategorySelected'];
                    }
                )
            ),
            'productsAvailability' => $this->getProductsAvailability($catalogStructure),
        ];
    }

    /**
     * Get products availability data.
     *
     * @param array $catalogStructure
     * @return array
     */
    private function getProductsAvailability($catalogStructure)
    {
        $productsAvailability = [];
        foreach ($catalogStructure as $categoryData) {
            foreach ($categoryData['products'] as $product) {
                $productsAvailability[] = [
                    'available' => $categoryData['isCategorySelected'] && $categoryData['isProductsSelected'],
                    'product' => $product,
                ];
            }
        }
        return $productsAvailability;
    }

    /**
     * Get nesting levels of categories in the top menu.
     *
     * @param array $catalogStructure
     * @return array
     */
    private function getCategoryLevels(array $catalogStructure)
    {
        $categoryLevels = [];
        foreach ($catalogStructure as $categoryData) {
            if ($categoryData['isCategorySelected']) {
                $categoryLevels[] = [
                    'level' => $categoryData['level'],
                    'category' => $categoryData['category'],
                ];
            }
        }
        return $categoryLevels;
    }

    /**
     * Create categories with products.
     *
     * @param array $catalogData
     * @param Category|null $parentCategory [optional]
     * @param int $level [optional]
     * @return array
     */
    private function prepareCatalog(array $catalogData, Category $parentCategory = null, $level = 0)
    {
        $data = [];
        foreach ($catalogData as $categoryData) {
            $dataItem = [
                'isCategorySelected' => $categoryData['isCategorySelected'],
                'isProductsSelected' => $categoryData['isProductsSelected'],
                'isLast' => !empty($categoryData['isLast']),
                'level' => $level,
            ];
            $category = $this->fixtureFactory->createByCode(
                'category',
                [
                    'dataset' => $categoryData['dataset'],
                    'data' => $parentCategory ? ['parent_id' => ['dataset' => '-', 'source' => $parentCategory]] : [],
                ]
            );
            $category->persist();
            $dataItem['category'] = $category;
            $dataItem['products'] = [];
            if (!empty($categoryData['products'])) {
                foreach (explode(',', $categoryData['products']) as $product) {
                    $productDataSet = explode('::', $product);
                    $productFixture = $this->fixtureFactory->createByCode(
                        $productDataSet[0],
                        [
                            'dataset' => $productDataSet[1],
                            'data' => ['category_ids' => ['dataset' => null, 'category' => $category]],
                        ]
                    );
                    $productFixture->persist();
                    $dataItem['products'][] = $productFixture;
                }
            }
            $data[] = $dataItem;
            if (!empty($categoryData['children'])) {
                $data = array_merge(
                    $data,
                    $this->prepareCatalog(
                        $categoryData['children'],
                        $category,
                        $categoryData['isCategorySelected'] ? $level + 1 : $level
                    )
                );
            }
        }
        return $data;
    }

    /**
     * Clean cache in admin panel.
     *
     * @return void
     */
    private function cleanCache()
    {
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Log out customer and roll back config settings.
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
