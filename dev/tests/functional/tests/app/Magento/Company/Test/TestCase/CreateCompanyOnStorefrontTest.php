<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\Adminhtml\ConfigCompanySetup;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create one customer without company.
 *
 * Steps:
 * 1. Login as a customer.
 * 2. Navigate to My Company.
 * 3. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68310
 */
class CreateCompanyOnStorefrontTest extends Injectable
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
     * Enable/disable Company Registration.
     *
     * @param Customer $customer
     * @param int $isButtonVisible
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        $isButtonVisible,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();

        // Steps:
        $this->loginCustomer($customer);
        $this->companyPage->open();

        return [
            'isButtonVisible' => $isButtonVisible
        ];
    }

    /**
     * Roll back config settings.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
