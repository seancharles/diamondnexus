<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;
use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Preconditions:
 * 1. Company and Shared Catalog B2B features are enabled (Stores > Configuration > B2B Features)
 * 2. Configurable product with two options (Red, Green) created
 * 3. Custom Shared Catalog created
 * 4. Created configurable product with all configurable options assigned to custom Shared Catalog
 * 5. New frontend customer created
 * 6. New company created with assigned existing customer as a sales rep
 * 7. Created company assigned to shared catalog
 *
 * Steps:
 * 1. On the front end, log in as this customer and add the configurable product to cart
 * 2. In the admin panel delete simple product assigned to this configurable.
 * 3. Perform assertions.
 *
 * @group    NegotiableQuote
 * @ZephyrId MAGETWO-89400
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteProductAfterAddingToQuoteTest extends Injectable
{
    /**
     * @var LogoutCustomerOnFrontendStep
     */
    private $logoutCustomerOnFrontend;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var SharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var SharedCatalogCompany
     */
    private $sharedCatalogCompany;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var SharedCatalogConfigure
     */
    private $sharedCatalogConfigure;

    /**
     * @var string
     */
    private $configData;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var string
     */
    private $company;

    /**
     * @var array
     */
    private $products;

    /**
     * @var SharedCatalog
     */
    private $sharedCatalog;

    /**
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Inject pages required for test.
     *
     * @param LogoutCustomerOnFrontendStep $logout
     * @param FixtureFactory               $fixtureFactory
     * @param SharedCatalogIndex           $sharedCatalogIndex
     * @param SharedCatalogCompany         $sharedCatalogCompany
     * @param Indexer                      $indexer
     * @param Cache                        $cache
     * @param SharedCatalogConfigure       $sharedCatalogConfigure
     * @param CatalogProductIndex          $catalogProductIndex
     * @return array
     */
    public function __inject(
        LogoutCustomerOnFrontendStep $logout,
        FixtureFactory $fixtureFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCompany $sharedCatalogCompany,
        Indexer $indexer,
        Cache $cache,
        SharedCatalogConfigure $sharedCatalogConfigure,
        CatalogProductIndex $catalogProductIndex
    ) {
        $this->logoutCustomerOnFrontend = $logout;
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
        $this->indexer = $indexer;
        $this->cache = $cache;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->catalogProductIndex = $catalogProductIndex;
    }

    /**
     * Test that shared catalog works fine with after products deletion.
     *
     * @param string        $configData
     * @param Customer      $customer
     * @param string        $company
     * @param array         $products
     * @param SharedCatalog $sharedCatalog
     * @return array
     */
    public function test(
        string $configData,
        Customer $customer,
        string $company,
        array $products,
        SharedCatalog $sharedCatalog
    ) {
        $this->configData = $configData;
        $this->customer = $customer;
        $this->company = $company;
        $this->products = $products;
        $this->sharedCatalog = $sharedCatalog;

        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Preconditions:
        // Configurable product is created in the system
        $createdProducts = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $this->products]
        )->run()['products'];

        // Company account exists in the system (General customer group)
        $createCompanyAdminStep = $this->objectManager->create(
            \Magento\NegotiableQuote\Test\TestStep\CreateCompanyAdminStep::class,
            [
                'customer' => $this->customer,
                'company'  => $this->company,
            ]
        )->run();

        $company = $createCompanyAdminStep['company'];
        $customer = $createCompanyAdminStep['customer'];

        // Shared Catalog created
        $this->sharedCatalog->persist();

        // Login to AP
        // Go to Catalog-> Shared Catalog-> select 'Assign Companies' for Default catalog
        // Find company created in preconditions -> click "Assign" -> click "Save"
        $this->assignCompanyToSharedCatalog($company);

        // Go to Catalog-> Shared Catalog-> select 'Set Pricing and Structure' for Default catalog
        // Click 'Configure' -> toggle on simple and configurable prods in the grid -> Next -> Generate -> Save
        $this->assignProductsToSharedCatalog($createdProducts);

        $this->cache->flush();
        $this->indexer->reindex();

        // Login to SF as a company admin
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        // Add products into shopping cart
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $createdProducts]
        )->run();

        $deletedProducts = $this->deleteProducts($createdProducts);

        return [
            'product'       => $deletedProducts,
            'sharedCatalog' => $sharedCatalog,
        ];
    }

    /**
     * Remove products.
     *
     * @param array $createdProducts
     * @return array
     */
    private function deleteProducts(array $createdProducts)
    {
        $configurableProduct = current($createdProducts);

        $configurableAttributesData = $configurableProduct->getConfigurableAttributesData();
        $simpleProductSkuToDelete = next($configurableAttributesData['matrix']);
        $productSkusToDelete = [
            ['sku' => $simpleProductSkuToDelete['sku']]
        ];

        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->massaction($productSkusToDelete, 'Delete', true);

        return $productSkusToDelete;
    }

    /**
     * Assign given company to the Shared Catalog.
     *
     * @param \Magento\Company\Test\Fixture\Company $company
     */
    private function assignCompanyToSharedCatalog(\Magento\Company\Test\Fixture\Company $company)
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $this->sharedCatalog->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $company->getCompanyName()]);
        $companyId = $this->sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $this->sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);
        $this->sharedCatalogCompany->getPageActions()->save();
    }

    /**
     * Assign products to the shared catalog.
     *
     * @param array $products
     */
    private function assignProductsToSharedCatalog(array $products)
    {
        $skuList = [];

        foreach ($products as $product) {
            if ($product instanceof ConfigurableProduct) {
                $configurableAttributes = (array)$product->getDataFieldConfig('configurable_attributes_data');

                if (!isset($configurableAttributes['source'])) {
                    continue;
                }

                if (!$configurableAttributes['source'] instanceof \Magento\Mtf\Fixture\DataSource) {
                    continue;
                }

                if (empty($configurableAttributes['source']->getData()['matrix'])) {
                    continue;
                }

                $matrix = (array)$configurableAttributes['source']->getData()['matrix'];
                foreach ($matrix as $childProduct) {
                    $skuList[] = $childProduct['sku'];
                }
            }
            $skuList[] = $product->getSku();
        }
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $this->sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->openConfigure($this->sharedCatalogIndex->getGrid()->getFirstItemId());

        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($skuList as $sku) {
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $sku]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->logoutCustomerOnFrontend->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
