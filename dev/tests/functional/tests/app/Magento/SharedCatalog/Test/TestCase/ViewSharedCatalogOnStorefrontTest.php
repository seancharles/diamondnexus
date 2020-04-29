<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
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
 * 1. Login on the storefront.
 * 2. Perform assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68029
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewSharedCatalogOnStorefrontTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Url to command.php.
     */
    const URL = 'dev/tests/functional/utils/command.php';

    /**
     * Id default category.
     *
     * @var int
     */
    private $defaultCategoryId = 2;

    /**
     * Tree categories.
     *
     * @var array
     */
    private $categories = [
        'default_subcategory',
        'two_nested_categories',
        'three_nested_categories',
    ];

    /**
     * Map categories for products.
     *
     * @var array
     */
    private $mapCategoriesForProducts = [
        'catalogProductSimple::sc_product_nesting_1' => 0,
        'catalogProductSimple::sc_product_nesting_2' => 1,
        'catalogProductSimple::sc_product_nesting_3' => 2,
        'catalogProductSimple::sc_product_nesting_3_disabled' => 2,
    ];

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var SharedCatalogCreate $sharedCatalogCreate
     */
    private $sharedCatalogCreate;

    /**
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
     *
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @param Indexer $indexer
     * @param AdminCache $adminCache
     * @return void
     */
    public function __inject(
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate,
        Indexer $indexer,
        AdminCache $adminCache
    ) {
        $this->objectManager = $objectManager;
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCreate = $sharedCatalogCreate;
        $this->indexer = $indexer;
        $this->adminCache = $adminCache;
    }

    /**
     * View SharedCatalog on the Storefront.
     *
     * @param array $productsList
     * @param Customer $customer
     * @param SharedCatalog $sharedCatalog
     * @param array $data [optional]
     * @param string $configData [optional]
     * @return array
     */
    public function test(
        array $productsList,
        Customer $customer,
        SharedCatalog $sharedCatalog,
        array $data,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            [
                'configData' => $configData,
                'flushCache' => true
            ]
        )->run();
        $sharedCatalog->persist();
        $products = $this->persistProducts($productsList);
        $emptyCategory = $this->addCategory(2);
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $company->persist();
        $this->assignCompany($sharedCatalog, $company);
        $this->configureSharedCatalog($sharedCatalog, array_slice($products, 0, 4), $data);
        $this->cleanCache();
        $this->indexer->reindex();
        $this->loginCustomer($customer);

        return [
            'productsPresentInCatalog' => array_slice($products, 0, 3),
            'productsAbsentInCatalog' => array_slice($products, 4),
            'productsPresentOnCategoryPage' => array_slice($products, 0, 1),
            'productsAbsentOnCategoryPage' => array_slice($products, 1),
            'productsDisabled' => [$products[3]],
            'emptyCategoriesAbsentInCatalog' => [$emptyCategory],
            'discount' => $data['discount'],
        ];
    }

    /**
     * Create tree.
     *
     * @param int $index
     * @return Category
     */
    private function addCategory($index)
    {
        $categoryItem = null;
        $parentId = $this->defaultCategoryId;
        for ($i = 0; $i <= $index; $i++) {
            $categoryItem = $this->fixtureFactory->createByCode(
                'category',
                [
                    'dataset' => $this->categories[$i],
                    'data' => [
                        'parent_id' => $parentId
                    ]
                ]
            );
            $categoryItem->persist();
            $parentId = $categoryItem->getId();
        }
        return $categoryItem;
    }

    /**
     * Add products to categories.
     *
     * @param array $productsList
     * @return array
     */
    private function persistProducts(array $productsList)
    {
        $products = [];
        foreach ($productsList as $product) {
            $productDataSet = explode('::', $product);
            $productItem = $this->fixtureFactory->createByCode(
                $productDataSet[0],
                [
                    'dataset' => $productDataSet[1],
                    'data' => [
                        'category_ids' => [
                            'category' => $this->addCategory($this->mapCategoriesForProducts[$product]),
                        ],
                    ]
                ]
            );
            $productItem->persist();
            $products[] = $productItem;
        }
        return $products;
    }

    /**
     * Assign shared catalog to a specified company.
     *
     * @param SharedCatalog $sharedCatalog
     * @param Company $company
     * @return void
     */
    private function assignCompany(SharedCatalog $sharedCatalog, Company $company)
    {
        $this->objectManager->create(
            'Magento\SharedCatalog\Test\TestStep\AssignCompanyStep',
            [
                'sharedCatalog' => $sharedCatalog,
                'company' => $company
            ]
        )->run();
    }

    /**
     * Configure shared catalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $products
     * @param array $data [optional]
     * @return void
     */
    private function configureSharedCatalog(SharedCatalog $sharedCatalog, array $products, array $data = [])
    {
        $this->objectManager->create(
            'Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep',
            [
                'sharedCatalog' => $sharedCatalog,
                'products' => $products,
                'data' => $data,
            ]
        )->run();
    }

    /**
     * Clean cache in admin panel.
     *
     * @return void
     */
    protected function cleanCache()
    {
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    private function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }

    /**
     * Logout customer from Storefront account and roll back config settings.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep'
        )->run();
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
