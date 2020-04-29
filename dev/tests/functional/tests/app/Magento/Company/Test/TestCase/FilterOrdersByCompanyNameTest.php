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
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;

/**
 * Preconditions:
 * 1. Create customers.
 * 2. Create companies.
 * 3. Create orders.
 *
 * Steps:
 * 1. Open Admin.
 * 2. Go to Sales > Orders.
 * 3. Add some columns.
 * 4. Perform all assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68597
 */
class FilterOrdersByCompanyNameTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Order grid page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Inject.
     *
     * @param OrderIndex $orderIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        OrderIndex $orderIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->orderIndex = $orderIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Filter orders by Company Name.
     *
     * @param string $configData
     * @param array $customers
     * @param string|null $checkedFields [optional]
     * @return array
     */
    public function test(
        $configData,
        array $customers,
        $checkedFields = null
    ) {

        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();

        $companiesData = [];
        $orders = [];
        $orderStatuses = [];
        foreach ($customers as $customer) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => $customer['dataset']]);
            $customer->persist();
            $company = $this->createCompany($customer);
            $order = $this->createOrder($customer);
            $companiesData[] = ['admin' => $customer, 'company' => $company, 'order' => $order];
            $orders[] = $order;
            $orderStatuses[] = 'Pending';
        }
        $this->orderIndex->open();
        $this->orderIndex->getStructureGrid()->clickResetButton();

        if ($checkedFields) {
            $this->orderIndex->getStructureGrid()->checkFieldsInColumnsMenu($checkedFields);
        }

        return [
            'companiesData' => $companiesData,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses
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
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }

    /**
     * Create company for user.
     *
     * @param Customer $customer
     * @return \Magento\Company\Test\Fixture\Company
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

        return $company;
    }

    /**
     * Create order for user.
     *
     * @param Customer $customer
     * @return \Magento\Sales\Test\Fixture\OrderInjectable
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

        return $order;
    }
}
