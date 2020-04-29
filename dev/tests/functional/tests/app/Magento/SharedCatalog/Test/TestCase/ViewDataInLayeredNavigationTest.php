<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preconditions:
 * 1. Create shared catalog.
 * 2. Create customer.
 * 3. Create company.
 * 4. Assign the customer as a company admin.
 * 5. Create three products with a category.
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Add products to shared catalog.
 * 3. Assign shared catalog to a company.
 * 4. Reindex data.
 * 5. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68047
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewDataInLayeredNavigationTest extends AbstractSharedCatalogConfigurationTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Category fixture.
     *
     * @var Category
     */
    private $category;

    /**
     * Shared catalog company.
     *
     * @var SharedCatalogCompany
     */
    private $sharedCatalogCompany;

    /**
     * Cms index.
     *
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * Page AdminCache.
     *
     * @var AdminCache
     */
    private $adminCache;

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $curlTransport;

    /**
     * @var string
     */
    private $configData;

    /**
     * @var string
     */
    private $url = 'dev/tests/functional/utils/command.php';

    /**
     * Inject.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @param AdminCache $adminCache
     * @param CmsIndex $cmsIndex
     * @param CurlTransport $curlTransport
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalogCompany $sharedCatalogCompany,
        AdminCache $adminCache,
        CmsIndex $cmsIndex,
        CurlTransport $curlTransport
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
        $this->adminCache = $adminCache;
        $this->cmsIndex = $cmsIndex;
        $this->curlTransport = $curlTransport;
    }

    /**
     * Test.
     *
     * @param Customer $customer
     * @param SharedCatalog $sharedCatalog
     * @param Category $category
     * @param array $productsList
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        SharedCatalog $sharedCatalog,
        Category $category,
        array $productsList,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $this->category = $category;
        $this->category->persist();
        $products = [];
        foreach ($productsList as $productItem) {
            $products[] = $this->createProduct($productItem);
        }
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
        $this->loginCustomer($customer);
        $sharedCatalog->persist();
        $catalogProduct = $products[0];
        $categoryName = $catalogProduct->getData('category_ids')[0];
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $this->sharedCatalogConfigure
            ->getStructureJstree()
            ->setTreeType('structure')
            ->expandAll()
            ->toggleNode($categoryName);

        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();

        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $company->getCompanyName()]);
        $companyId = $this->sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $this->sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);
        if ($this->sharedCatalogCompany->getModalBlock()->isVisible()) {
            $this->sharedCatalogCompany->getModalBlock()->acceptAlert();
        }
        $this->sharedCatalogCompany->getPageActions()->save();
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdateCategoryPermissions');
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');
        $this->reindex();
        $this->logoutCustomer($customer);
        $this->cleanCache();
        $this->loginCustomer($customer);
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        return [
            'productToDisable' => $products[1],
            'categoryName' => $categoryName
        ];
    }

    /**
     * Create product.
     *
     * @param string $productType
     * @return FixtureInterface
     */
    private function createProduct($productType)
    {
        list($fixture, $dataset) = explode('::', $productType);
        $product = $this->fixtureFactory->createByCode(
            $fixture,
            [
                'dataset' => $dataset,
                'data' => [
                    'category_ids' => [
                        'category' => $this->category,
                    ],
                ]
            ]
        );
        $product->persist();

        return $product;
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
     * Logout customer.
     *
     * @param Customer $customer
     * @return void
     */
    private function logoutCustomer(Customer $customer)
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep',
            ['customer' => $customer]
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
     * Reindex.
     *
     * @throws \Exception
     * @return void
     */
    private function reindex()
    {
        $command = 'indexer:reindex';
        $this->curlTransport->write($this->prepareUrl($command), [], CurlInterface::GET);
        $this->curlTransport->read();
        $this->curlTransport->close();
    }

    /**
     * Prepare url.
     *
     * @param string $command
     * @param array $options
     * @return string
     */
    private function prepareUrl($command, array $options = [])
    {
        $command .= ' ' . implode(' ', $options);
        return $_ENV['app_frontend_url'] . $this->url . '?command=' . urlencode($command);
    }
}
