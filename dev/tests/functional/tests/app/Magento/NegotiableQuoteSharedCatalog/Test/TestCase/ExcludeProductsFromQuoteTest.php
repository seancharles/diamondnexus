<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuoteSharedCatalog\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
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

/**
 * Preconditions:
 * 1. Enable "Shared Catalog", "Company" and "Negotiable Quote" features in Stores->Configuration->General->B2B Features
 * 2. Simple product is created in the system
 * 3. Configurable product is created in the system
 * 4. Company account exists in the system (General customer group)
 * 5. Shared Catalog created
 *
 * Steps:
 * 1. Login to AP
 * 2. Go to Catalog-> Shared Catalog-> select 'Assign Companies' for Default catalog
 * 3. Find company created in preconditions -> click "Assign" -> click "Save"
 * 4. Go to Catalog-> Shared Catalog-> select 'Set Pricing and Structure' for Default catalog
 * 5. Click 'Configure' -> toggle on simple and configurable prods in the grid -> Next -> Generate -> Save
 * 6. Login to SF as a company admin
 * 7. Add simple and configurable products into shopping cart
 * 8. Request negotiable quote
 * 9. Login to AP
 * 10. Go to Catalog-> Shared Catalog-> select 'Set Pricing and Structure' for Default catalog
 * 11. Click 'Configure' -> toggle off simple and configurable products in the grid -> Next -> Generate -> Save
 *
 * @group    NegotiableQuoteSharedCatalog
 * @ZephyrId MAGETWO-72355
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExcludeProductsFromQuoteTest extends Injectable
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
     * Inject pages required for test.
     *
     * @param LogoutCustomerOnFrontendStep $logout
     * @param FixtureFactory               $fixtureFactory
     * @param SharedCatalogIndex           $sharedCatalogIndex
     * @param SharedCatalogCompany         $sharedCatalogCompany
     * @param Indexer                      $indexer
     * @param Cache                        $cache
     * @param SharedCatalogConfigure       $sharedCatalogConfigure
     */
    public function __inject(
        LogoutCustomerOnFrontendStep $logout,
        FixtureFactory $fixtureFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCompany $sharedCatalogCompany,
        Indexer $indexer,
        Cache $cache,
        SharedCatalogConfigure $sharedCatalogConfigure
    ) {
        $this->logoutCustomerOnFrontend = $logout;
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
        $this->indexer = $indexer;
        $this->cache = $cache;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
    }

    /**
     * Test that shared catalog works fine with products that are in negotiable quotes of company account.
     *
     * @param string        $configData
     * @param Customer      $customer
     * @param string        $company
     * @param array         $products
     * @param SharedCatalog $sharedCatalog
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
        // 1. Simple product is created in the system
        // 2. Configurable product is created in the system
        $createdProducts = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $this->products]
        )->run()['products'];

        // 3. Company account exists in the system (General customer group)
        $createCompanyAdminStep = $this->objectManager->create(
            \Magento\NegotiableQuote\Test\TestStep\CreateCompanyAdminStep::class,
            [
                'customer' => $this->customer,
                'company'  => $this->company,
            ]
        )->run();
        $company = $createCompanyAdminStep['company'];
        $customer = $createCompanyAdminStep['customer'];

        // 4. Shared Catalog created
        $this->sharedCatalog->persist();

        // Steps:
        // 1. Login to AP
        // 2. Go to Catalog-> Shared Catalog-> select 'Assign Companies' for Default catalog
        // 3. Find company created in preconditions -> click "Assign" -> click "Save"
        $this->assignCompanyToSharedCatalog($company);

        // 4. Go to Catalog-> Shared Catalog-> select 'Set Pricing and Structure' for Default catalog
        // 5. Click 'Configure' -> toggle on simple and configurable prods in the grid -> Next -> Generate -> Save
        $this->assignProductsToSharedCatalog($createdProducts);

        $this->cache->flush();
        $this->indexer->reindex();

        // 6. Login to SF as a company admin
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        // 7. Add simple and configurable products into shopping cart
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $createdProducts]
        )->run();

        // 8. Request negotiable quote
        $this->objectManager->create(
            \Magento\NegotiableQuote\Test\TestStep\RequestQuoteStep::class
        )->run();

        //  9. Login to AP
        // 10. Go to Catalog-> Shared Catalog-> select 'Set Pricing and Structure' for Default catalog
        // 11. Click 'Configure' -> toggle off simple and configurable products in the grid -> Next -> Generate -> Save
        $this->unassignProductsFromSharedCatalog($createdProducts);
    }

    /**
     * Assign products to the shared catalog
     *
     * @param array $products
     */
    private function assignProductsToSharedCatalog(array $products)
    {
        $this->configSharedCatalog($products, true);
    }

    /**
     * Remove products assignment.
     *
     * @param array $products
     */
    private function unassignProductsFromSharedCatalog(array $products)
    {
        $this->configSharedCatalog($products, false);
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
     * Configure shared catalog products and pricing.
     *
     * @param array $products
     * @param bool  $assign
     */
    private function configSharedCatalog(array $products, bool $assign)
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
            if ($assign === true) {
                $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $sku]);
            } else {
                $this->sharedCatalogConfigure->getStructureGrid()->uncheckSwitcherItem(['sku' => $sku]);
            }
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
