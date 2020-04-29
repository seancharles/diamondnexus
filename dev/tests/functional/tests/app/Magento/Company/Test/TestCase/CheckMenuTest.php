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
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company.
 * 3. Create order.
 *
 * Steps:
 * 1. Login as a customer to the SF.
 * 2. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68378
 */
class CheckMenuTest extends AbstractCompanyTest
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * CustomerAccountIndex page.
     *
     * @var CustomerAccountIndex
     */
    private $customerAccountIndex;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Inject.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CustomerAccountIndex $customerAccountIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->customerAccountIndex = $customerAccountIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test check menu for B2B and B2C users.
     *
     * @param Customer $customer
     * @param string $configData
     * @param int $hasCompany
     * @param array $myAccountMenuLinks
     * @param array $headerMenuLinks
     * @param array $sidebarBlocks
     * @return array
     */
    public function test(
        Customer $customer,
        $configData,
        $hasCompany,
        array $myAccountMenuLinks,
        array $headerMenuLinks,
        array $sidebarBlocks
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $configData]
        )->run();
        $customer->persist();
        $this->customer = $customer;
        if ($hasCompany) {
            $this->createCompany($customer);
        }
        $this->createOrder($customer);
        $this->loginCustomer($customer);

        return [
            'myAccountMenuLinks' => $myAccountMenuLinks,
            'headerMenuLinks' => $headerMenuLinks,
            'sidebarBlocks' => $sidebarBlocks
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

    /**
     * Create company for user.
     *
     * @param Customer $customer
     * @return void
     */
    private function createCompany(Customer $customer)
    {
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $company->persist();
    }

    /**
     * Create order for user.
     *
     * @param Customer $customer
     * @return void
     */
    private function createOrder(Customer $customer)
    {
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => 'default',
                'data' => ['customer_id' => ['customer' => $customer]]
            ]
        );
        $order->persist();
    }
}
