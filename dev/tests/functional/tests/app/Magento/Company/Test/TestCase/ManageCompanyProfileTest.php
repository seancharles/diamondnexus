<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\CompanyProfile as CompanyProfilePage;
use Magento\Company\Test\Page\CompanyProfileEdit;

/**
 * Preconditions:
 * 1. Create customer with company.
 * 2. Create customer without company.
 *
 * Steps:
 * 1. Login as company admin on the Storefront.
 * 2. Navigate to My Company.
 * 3. Click on "Add Customer" link.
 * 4. Fill out all data according to data set.
 * 5. Save customer.
 * 6. Edit company address.
 * 6. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68206
 */
class ManageCompanyProfileTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Company page
     *
     * @var CompanyPage $companyPage
     */
    protected $companyPage;

    /**
     * Company profile page
     *
     * @var CompanyProfilePage $companyProfilePage
     */
    protected $companyProfilePage;

    /**
     * Company profile edit page
     *
     * @var CompanyProfileEdit $companyProfileEdit
     */
    protected $companyProfileEdit;

    /**
     * Fixture factory
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Perform needed injections
     *
     * @param CompanyPage $companyPage
     * @param CompanyProfilePage $companyProfilePage
     * @param CompanyProfileEdit $companyProfileEdit
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(
        CompanyPage $companyPage,
        CompanyProfilePage $companyProfilePage,
        CompanyProfileEdit $companyProfileEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->companyPage = $companyPage;
        $this->companyProfilePage = $companyProfilePage;
        $this->companyProfileEdit = $companyProfileEdit;
        $this->fixtureFactory = $fixtureFactory;
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
     * Test manage company profile
     *
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param string $configData
     * @param int $isAdmin
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        Customer $companyUser,
        $configData = null,
        $isAdmin = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $companyAdmin->persist();
        $companyUser->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $company->persist();
        $customer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'email' => $companyUser->getEmail(),
                ],
            ]
        );
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->fill($customer);
        $this->companyPage->getCustomerPopup()->setJobTitle($customer->getJobTitle());
        $this->companyPage->getCustomerPopup()->setTelephone($customer->getTelephone());
        $this->companyPage->getCustomerPopup()->submit();
        if ($isAdmin) {
            $this->companyProfilePage->open();
            $this->companyProfilePage->getProfileContent()->clickEditButton();
            $companyUpdate = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => 'company_fixture_for_update',
                ]
            );
            $this->companyProfileEdit->getCompanyProfileForm()->fill($companyUpdate);
            $this->companyProfileEdit->getCompanyProfileForm()->submit();
        }

        return [
            'company' => isset($companyUpdate) ? $companyUpdate : null,
            'companyUser' => $companyUser
        ];
    }

    /**
     * Logout customer from Storefront account and roll back config settings.
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
