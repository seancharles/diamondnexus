<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create two customers with companies.
 * 2. Create one customer without company.
 *
 * Steps:
 * 1. Login as the first company admin on the Storefront.
 * 2. Navigate to My Company.
 * 3. Click on "Add Customer" link.
 * 4. Fill out all data according to data set.
 * 5. Save customer.
 * 6. Perform assertions
 *
 * @group Company
 * @ZephyrId MAGETWO-68219
 */
class AddUserToCompanyOnStorefrontTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Company page.
     *
     * @var CompanyPage $companyPage
     */
    protected $companyPage;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

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
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
    }

    /**
     * Add existing user to company test.
     *
     * @param Customer $firstCompanyAdmin
     * @param Customer $secondCompanyAdmin
     * @param Customer $userWithoutCompany
     * @param int $hasCompany
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $firstCompanyAdmin,
        Customer $secondCompanyAdmin,
        Customer $userWithoutCompany,
        $hasCompany = null,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $firstCompanyAdmin->persist();
        $secondCompanyAdmin->persist();
        if ($hasCompany) {
            $userWithoutCompany = $secondCompanyAdmin;
        }
        $firstCompany = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $firstCompanyAdmin->getEmail(),
                ],
            ]
        );
        $secondCompany = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $secondCompanyAdmin->getEmail(),
                ],
            ]
        );
        $firstCompany->persist();
        $secondCompany->persist();
        $customer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_job_phone',
                'data' => [
                    'email' => $userWithoutCompany->getEmail(),
                    'firstname' => $userWithoutCompany->getFirstname(),
                    'lastname' => $userWithoutCompany->getLastname()
                ],
            ]
        );
        $this->loginCustomer($firstCompanyAdmin);
        $this->companyPage->open();
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->fill($customer);
        $this->companyPage->getCustomerPopup()->setJobTitle($customer->getJobTitle());
        $this->companyPage->getCustomerPopup()->setTelephone($customer->getTelephone());
        if (!$hasCompany) {
            $this->companyPage->getCustomerPopup()->submit();
        }

        return [
            'customer' => $userWithoutCompany,
            'customersCompany' => [$userWithoutCompany],
            'companyName' => $firstCompany->getCompanyName()
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
