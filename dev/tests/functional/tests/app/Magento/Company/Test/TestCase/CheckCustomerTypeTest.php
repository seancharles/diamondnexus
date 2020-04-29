<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Preconditions:
 * 1. Create customer without company.
 * 2. Create company.
 * 3. Create company admin.
 * 4. Create company user.
 *
 * Steps:
 * 1. Add company user to company.
 * 2. Login to admin panel.
 * 3. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68306, @ZephyrId MAGETWO-68308
 */
class CheckCustomerTypeTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Company page.
     *
     * @var CompanyPage
     */
    protected $companyPage;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject.
     *
     * @param CompanyPage $companyPage
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CompanyPage $companyPage,
        FixtureFactory $fixtureFactory
    ) {
        $this->companyPage = $companyPage;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test check customer type test.
     *
     * @param Customer $customer
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        Customer $companyAdmin,
        Customer $companyUser,
        $configData = null
    ) {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $configData]
        )->run();
        $customer->persist();
        $companyAdmin->persist();
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
        $companyUserCustomer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'email' => $companyUser->getEmail(),
                    'firstname' => $companyUser->getFirstname(),
                    'lastname' => $companyUser->getLastname()
                ],
            ]
        );
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->fill($companyUserCustomer);
        $this->companyPage->getCustomerPopup()->setJobTitle($companyUserCustomer->getJobTitle());
        $this->companyPage->getCustomerPopup()->setTelephone($companyUserCustomer->getTelephone());
        $this->companyPage->getCustomerPopup()->submit();

        return [
            'customer' => $customer,
            'companyAdmin' => $companyAdmin,
            'companyUser' => $companyUser,
            'company' => $company
        ];
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
}
