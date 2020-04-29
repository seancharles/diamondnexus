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
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;

/**
 * Preconditions:
 * 1. Create company with customer.
 * 2. Create order with Payment on Account method.
 *
 * Steps:
 * 1. Change company status to Blocked.
 * 2. Create invoice for the order.
 * 3. Create Credit Memo for the order.
 * 4. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68424
 */
class PaymentOnAccountRefundTest extends AbstractCompanyCreditTest
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
     * New order memo page.
     *
     * @var \Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew
     */
    private $orderCreditMemoNew;

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyEdit $companyEdit
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyEdit $companyEdit,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderCreditMemoNew $orderCreditMemoNew
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyEdit = $companyEdit;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
    }

    /**
     * Test refund to blocked company for Payment on Account method.
     *
     * @param string $companyDataset
     * @param string $companyStatus
     * @param Customer $customer
     * @param string $orderInjectable
     * @param array $refundData
     * @param string|null $configData
     * @return array
     */
    public function test(
        $companyDataset,
        $companyStatus,
        Customer $customer,
        $orderInjectable,
        array $refundData,
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
        $this->companyEdit->open(['id' => $company->getId()]);
        $this->companyEdit->getCompanyForm()->setCompanyStatus($companyStatus);
        $this->companyEdit->getFormPageActions()->save();
        $products = $order->getEntityId()['products'];
        $cart['data']['items'] = ['products' => $products];
        $this->createInvoice($order, $products, $this->fixtureFactory->createByCode('cart', $cart));
        $this->createCreditMemo($order, $refundData);

        return [
            'company' => $company,
            'orderId' => $order->getId(),
        ];
    }

    /**
     * Create credit memo.
     *
     * @param \Magento\Sales\Test\Fixture\OrderInjectable $order
     * @param array $data
     * @return array
     */
    private function createCreditMemo(\Magento\Sales\Test\Fixture\OrderInjectable $order, array $data = [])
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $this->salesOrderView->getPageActions()->orderCreditMemo();
        if (!empty($data)) {
            $this->orderCreditMemoNew->getFormBlock()->fillProductData(
                $data,
                $order->getEntityId()['products']
            );
            $this->orderCreditMemoNew->getFormBlock()->updateQty();
            $this->orderCreditMemoNew->getFormBlock()->fillFormData($data);
        }
        $this->orderCreditMemoNew->getFormBlock()->submitOffline();

        $this->salesOrderView->getOrderForm()->openTab('creditmemos');
        return [
            'creditMemoIds' => $this->salesOrderView->getOrderForm()->getTab('creditmemos')->getGridBlock()->getIds()
        ];
    }
}
