<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Preconditions:
 * 1. Create company with customer.
 * 2. Create order with Payment on Account method.
 *
 * Steps:
 * 1. Change company status to Pending Approval or delete company.
 * 2. Cancel the order.
 * 3. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68426
 */
class PaymentOnAccountRevertTest extends AbstractCompanyCreditTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Company edit page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyEdit
     */
    private $companyEdit;

    /**
     * Order index page.
     *
     * @var \Magento\Sales\Test\Page\Adminhtml\OrderIndex
     */
    private $orderIndex;

    /**
     * Order view page.
     *
     * @var \Magento\Sales\Test\Page\Adminhtml\SalesOrderView
     */
    private $salesOrderView;

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyEdit $companyEdit
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyEdit $companyEdit,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyEdit = $companyEdit;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
    }

    /**
     * Test revert functionality for Payment on Account method.
     *
     * @param string $companyDataset
     * @param Customer $customer
     * @param string $orderInjectable
     * @param string|null $companyStatus
     * @param bool $cancelOrder
     * @param bool $deleteCompany
     * @param string|null $configData
     * @return array
     */
    public function test(
        $companyDataset,
        Customer $customer,
        $orderInjectable,
        $companyStatus = null,
        $cancelOrder = false,
        $deleteCompany = false,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => $companyDataset,
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $company->persist();
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => $orderInjectable,
                'data' => ['customer_id' => ['customer' => $customer]]
            ]
        );
        $order->persist();

        //Steps:
        if ($companyStatus) {
            $this->companyEdit->open(['id' => $company->getId()]);
            $this->companyEdit->getCompanyForm()->setCompanyStatus($companyStatus);
            $this->companyEdit->getFormPageActions()->save();
        }

        if ($deleteCompany) {
            $deleteCompanyStep = $this->objectManager->create(
                \Magento\Company\Test\TestStep\DeleteCompanyStep::class,
                ['companyName' => $company->getCompanyName()]
            );
            $deleteCompanyStep->run();
        }

        if ($cancelOrder) {
            $this->orderIndex->open();
            $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
            $this->salesOrderView->getPageActions()->cancel();
        }

        return [
            'company' => $company,
            'orderId' => $order->getId(),
        ];
    }
}
