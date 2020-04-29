<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Fixture\Category;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Company\Test\Fixture\Company;
use Magento\Store\Test\Fixture\Website;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Util\Command\Cli\Indexer;

/**
 * Preconditions:
 * 1. Create products.
 * 2. Create company.
 * 3. Create shared catalog.
 * 4. Assign company to the shared catalog.
 * 5. Assign products to the shared catalog.
 *
 * Steps:
 * 1. Create Second website.
 * 2. Set fixed custom price for product for all websites.
 * 3. Set percent custom price for product for all websites.
 * 4. Set fixed custom price for product for second website using mass actions.
 * 5. Save shared catalog.
 * 6. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68571
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetCustomPricesMultipleWebsitesTest extends AbstractSharedCatalogConfigurationTest
{
    /**
     * Fixture factory.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * @var \Magento\Store\Test\Fixture\Website
     */
    private $website;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

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
     * Label of All Websites filter option.
     *
     * @var string
     */
    private $allWebsitesFilterOption = 'All Websites';

    /**
     * Label of Main Website filter option.
     *
     * @var string
     */
    private $mainWebsiteFilterOption = 'Main Website';

    /**
     * Inject pages.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param TestStepFactory $stepFactory
     * @param Indexer $indexer
     * @param AdminCache $adminCache
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        TestStepFactory $stepFactory,
        Indexer $indexer,
        AdminCache $adminCache
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->stepFactory = $stepFactory;
        $this->indexer = $indexer;
        $this->adminCache = $adminCache;
    }

    /**
     * Set custom prices for multiple websites.
     *
     * @param Category $category
     * @param SharedCatalog $sharedCatalog
     * @param array $productsList
     * @param array $customPrices
     * @param bool $saveSharedCatalog
     * @param Customer|null $customer [optional]
     * @param bool $createSecondWebsite [optional]
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        Category $category,
        SharedCatalog $sharedCatalog,
        array $productsList,
        array $customPrices,
        $saveSharedCatalog,
        Customer $customer = null,
        $createSecondWebsite = false,
        $configData = null
    ) {
        $websiteName = $this->mainWebsiteFilterOption;
        if ($createSecondWebsite) {
            $this->website = $this->createWebsite();
            $websiteName = $this->website->getName();
        }
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();

        if ($customer) {
            $customer->persist();
            $company = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => 'company_with_required_fields_and_sales_rep',
                    'data' => [
                        'email' => $customer->getEmail()
                    ]
                ]
            );
            $company->persist();
            $this->assignCompany($sharedCatalog, $company);
        }

        $category->persist();
        $products = $this->prepareProducts($category, $productsList);
        $this->stepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $sharedCatalog, 'products' => $products]
        )->run();
        $this->openSharedCatalogConfiguration($sharedCatalog);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($this->allWebsitesFilterOption);

        $customPricesData = [];
        foreach ($customPrices['simple'] as $key => $customPrice) {
            $this->setCustomPrice($products[$key], $customPrice);
            $customPricesData[$key] = $customPrice;
        }

        $this->sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($websiteName);

        if (!empty($customPrices['mass_action'])) {
            foreach ($customPrices['mass_action'] as $key => $customPrice) {
                $this->setCustomPriceViaMassActions($products[$key], $customPrice);
                $customPricesData[$key] = $customPrice;
            }
        }

        $this->sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($this->allWebsitesFilterOption);
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdateCategoryPermissions');

        if ($saveSharedCatalog) {
            $this->sharedCatalogConfigure->getNavigation()->nextStep();
            $this->sharedCatalogConfigure->getPageActionBlock()->save();
            $this->cleanCache();
            $this->indexer->reindex();
        }

        return [
            'customer' => $customer,
            'products' => $products,
            'customPrices' => $customPricesData,
            'website' => $this->website,
            'websiteName' => $websiteName
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
            $productDataSet = explode('::', $product);
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

    /**
     * Set custom price.
     *
     * @param \Magento\Catalog\Test\Fixture\CatalogProductSimple $product
     * @param array $priceData
     * @return void
     */
    private function setCustomPrice(\Magento\Catalog\Test\Fixture\CatalogProductSimple $product, array $priceData)
    {
        $this->sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);
        $this->sharedCatalogConfigure->getPricingGrid()->setCustomPriceType($priceData['type']);
        $this->sharedCatalogConfigure->getPricingGrid()->setCustomPrice($priceData['value']);
    }

    /**
     * Set discount or adjust fixed price via mass actions.
     *
     * @param \Magento\Catalog\Test\Fixture\CatalogProductSimple $product
     * @param array $priceData
     * @return void
     */
    private function setCustomPriceViaMassActions(
        \Magento\Catalog\Test\Fixture\CatalogProductSimple $product,
        array $priceData
    ) {
        $this->sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);

        if ($priceData['type'] == 'Discount') {
            $this->sharedCatalogConfigure->getPricingGrid()->applyDiscount();
        } else {
            $this->sharedCatalogConfigure->getPricingGrid()->adjustFixedPrice();
        }

        $this->sharedCatalogConfigure->getDiscount()->setAlertText($priceData['value']);
        $this->sharedCatalogConfigure->getDiscount()->acceptAlert();
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
            \Magento\SharedCatalog\Test\TestStep\AssignCompanyStep::class,
            [
                'sharedCatalog' => $sharedCatalog,
                'company' => $company
            ]
        )->run();
    }

    /**
     * Create custom website.
     *
     * @return Website
     */
    private function createWebsite()
    {
        $storeGroup = $this->fixtureFactory->createByCode(
            'storeGroup',
            [
                'dataset' => 'custom_new_group',
                'data' => [
                    'root_category_id' => [
                        'dataset' => 'default_category'
                    ]
                ]
            ]
        );
        $storeGroup->persist();
        /** @var \Magento\Store\Test\Fixture\Store $storeFixture */
        $store = $this->fixtureFactory->createByCode(
            'store',
            [
                'dataset' => 'custom_store',
                'data' => [
                    'group_id' => [
                        'storeGroup' => $storeGroup
                    ]
                ]
            ]
        );
        $store->persist();

        return $storeGroup->getDataFieldConfig('website_id')['source']->getWebsite();
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
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->website) {
            $this->objectManager->create(
                \Magento\SharedCatalog\Test\TestStep\DeleteWebsiteStep::class,
                ['website' => $this->website]
            )->run();
        }
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
        parent::tearDown();
    }
}
