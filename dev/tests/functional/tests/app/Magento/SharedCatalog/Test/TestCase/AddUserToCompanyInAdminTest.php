<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Customer\Test\Fixture\Customer;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Preconditions:
 * 1. Create customer with company.
 * 2. Create customer without company.
 * 3. Create another company.
 * 4. Create two shared catalogs.
 * 5. Assign shared catalogs to companies.
 *
 * Steps:
 * 1. Open customer without company in the admin panel.
 * 2. Assign customer to the company with the user.
 * 3. Login as the first company admin to the Storefront.
 * 4. Navigate to "My company" tab.
 * 5. Open recently assigned customer in the admin panel again.
 * 6. Assign that customer to another company.
 * 7. Verify that customer group has changed accordingly.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68220
 */
class AddUserToCompanyInAdminTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Fixture factory
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Company page
     *
     * @var CompanyPage $companyPage
     */
    protected $companyPage;

    /**
     * Customers grid
     *
     * @var CustomerIndex $customerIndex
     */
    protected $customerIndex;

    /**
     * Customer edit page
     *
     * @var CustomerIndexEdit $customerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Configuration setting
     *
     * @var string
     */
    protected $configData;

    /**
     * Assign company to the specified customer
     *
     * @param Customer $customer
     * @param Customer $companyToAssign
     * @return void
     */
    protected function assignCompanyToCustomer(Customer $customer, Customer $companyToAssign)
    {
        $filter['email'] = $customer->getEmail();
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEdit->getCustomerForm()->openTab('account_information');
        $this->customerIndexEdit->getCustomerForm()->fill($companyToAssign);
        $this->customerIndexEdit->getCompanyModalBlock()->acceptAlert();
        $this->customerIndexEdit->getPageActionsBlock()->save();
    }

    /**
     * Assign shared catalog to a specified company
     *
     * @param SharedCatalog $sharedCatalog
     * @param Company $company
     * @return void
     */
    protected function assignCompany(SharedCatalog $sharedCatalog, Company $company)
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
     * Login customer
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param ObjectManager $objectManager
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        ObjectManager $objectManager
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->objectManager = $objectManager;
    }

    /**
     * Assign user to company with shared catalog
     *
     * @param Customer $customer
     * @param Customer $companyAdmin
     * @param SharedCatalog $firstSharedCatalog
     * @param SharedCatalog $secondSharedCatalog
     * @param Company $secondCompany
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        Customer $companyAdmin,
        SharedCatalog $firstSharedCatalog,
        SharedCatalog $secondSharedCatalog,
        Company $secondCompany,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $companyAdmin->persist();
        $firstSharedCatalog->persist();
        $secondSharedCatalog->persist();
        $firstCompany = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $firstCompany->persist();
        $secondCompany->persist();
        $customerToFillForm = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default_with_company_association',
                'data' => [
                    'company_association' => $firstCompany->getCompanyName(),
                ],
            ]
        );
        $customerToFillForm2 = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default_with_company_association',
                'data' => [
                    'company_association' => $secondCompany->getCompanyName(),
                ],
            ]
        );
        $this->assignCompany($firstSharedCatalog, $firstCompany);
        $this->assignCompany($secondSharedCatalog, $secondCompany);
        $this->assignCompanyToCustomer($customer, $customerToFillForm);
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->assignCompanyToCustomer($customer, $customerToFillForm2);

        return [
            'customerGroup' => $secondSharedCatalog->getName(),
            'customer' => $customer
        ];
    }

    /**
     * Logout customer from Storefront account and roll back config settings
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
