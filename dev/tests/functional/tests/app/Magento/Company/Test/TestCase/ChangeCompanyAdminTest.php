<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Preconditions:
 * 1. Create customer with company and addresses.
 * 2. Create customer without company and addresses.
 *
 * Steps:
 * 1. Login to the admin panel and change company admin.
 * 2. Login to the Storefront as a new company admin.
 * 3. Go to the Address Book.
 * 4. Verify that addresses are copied from the previous company admin.
 *
 * @group Company
 * @ZephyrId MAGETWO-68241, @ZephyrId MAGETWO-67929
 */
class ChangeCompanyAdminTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration setting
     *
     * @var string
     */
    protected $configData;

    /**
     * Company index page
     *
     * @var CompanyIndex
     */
    protected $companyIndex;

    /**
     * Company edit page
     *
     * @var CompanyEdit
     */
    protected $companyEdit;

    /**
     * Login customer.
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
     * Change company admin
     *
     * @param \Magento\Company\Test\Fixture\Company $company
     * @param Customer $customer
     * @return void
     */
    protected function changeCompanyAdmin(\Magento\Company\Test\Fixture\Company $company, Customer $customer)
    {
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $this->companyEdit->getCompanyForm()->changeCompanyAdmin($customer);
        $this->companyEdit->getFormPageActions()->save();
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param \Magento\Company\Test\Page\Adminhtml\CompanyIndex $companyIndex
     * @param \Magento\Company\Test\Page\Adminhtml\CompanyEdit $companyEdit
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        \Magento\Company\Test\Page\Adminhtml\CompanyIndex $companyIndex,
        \Magento\Company\Test\Page\Adminhtml\CompanyEdit $companyEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Change company admin in the AP
     *
     * @param Customer $companyAdmin
     * @param Customer $customer
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        Customer $customer,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $companyAdmin->persist();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $company->persist();

        //Steps
        $this->changeCompanyAdmin($company, $customer);
        $this->loginCustomer($customer);

        return [
            'shippingAddress' => $companyAdmin->getDataFieldConfig('address')['source']->getAddresses()[0]
        ];
    }

    /**
     * Logout customer from frontend account and roll back config settings.
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
