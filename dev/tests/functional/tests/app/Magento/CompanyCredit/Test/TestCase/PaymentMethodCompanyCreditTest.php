<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;

/**
 * Preconditions:
 * 1. Create product.
 * 2. Create company.
 *
 * Steps:
 * 1. Enable "Payment On Account" and set New Order Status to Pending/Processing.
 * 2. Enable credit limit in company.
 * 3. Place order.
 * 4. Reimburse Balance.
 * 5. Create Invoice.
 * 6. Cancel Order.
 * 7. Do partial refund.
 * 8. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68317, @ZephyrId MAGETWO-68316, @ZephyrId MAGETWO-68355
 * @ZephyrId MAGETWO-68350, @ZephyrId MAGETWO-68379, @ZephyrId MAGETWO-68430
 *
 * @SuppressWarnings(PHPMD)
 */
class PaymentMethodCompanyCreditTest extends AbstractCompanyCreditTest
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
     * Company index page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyIndex
     */
    private $companyIndex;

    /**
     * Company edit page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyEdit
     */
    private $companyEdit;

    /**
     * System config payment section.
     *
     * @var \Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment
     */
    private $systemConfigEditSectionPayment;

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
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param SystemConfigEditSectionPayment $systemConfigEditSectionPayment
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderCreditMemoNew $orderCreditMemoNew
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        SystemConfigEditSectionPayment $systemConfigEditSectionPayment,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderCreditMemoNew $orderCreditMemoNew
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->systemConfigEditSectionPayment = $systemConfigEditSectionPayment;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
    }

    /**
     * Test manage company credit in admin panel.
     *
     * @param Customer $customer
     * @param array $checkout
     * @param array $amounts
     * @param string $companyPayment
     * @param string $companyCredit
     * @param string $expectedPaymentMethod
     * @param string $expectedOrderStatus
     * @param array $operations
     * @param string|null $orderInjectable
     * @param string|null $products
     * @param string|null $historyDataSet
     * @param bool $createInvoice
     * @param bool $cancelOrder
     * @param array $productsToRefund
     * @param string|null $expectedMethods
     * @param string|null $configData
     * @param int|null $enableCompanyCredit
     * @param string|null $commentsHistory [optional]
     * @return array
     */
    public function test(
        Customer $customer,
        array $checkout,
        array $amounts,
        $companyPayment,
        $companyCredit,
        $expectedPaymentMethod,
        $expectedOrderStatus,
        array $operations = [],
        $orderInjectable = null,
        $products = null,
        $historyDataSet = null,
        $createInvoice = false,
        $cancelOrder = false,
        array $productsToRefund = [],
        $expectedMethods = null,
        $configData = null,
        $enableCompanyCredit = null,
        $commentsHistory = null
    ) {
        // Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_status',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $companyPaymentFixture = $this->fixtureFactory->createByCode('company', ['dataset' => $companyPayment]);
        $companyCreditFixture = $this->fixtureFactory->createByCode(
            'company',
            ['dataset' => $companyCredit]
        );
        $company->persist();

        if (!empty($checkout['payment']['po_number'])) {
            $checkout['payment']['po_number'] .= time();
        }
        $products = $this->prepareProducts($products);
        // Steps:
        if ($enableCompanyCredit) {
            $this->systemConfigEditSectionPayment->open();
            $this->systemConfigEditSectionPayment->getPaymentAccount()->enable();
            $this->systemConfigEditSectionPayment->getPageActions()->save();
        }
        $this->companyIndex->open();
        $filter = ['company_name' => $company->getCompanyName()];
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getCompanyForm()->openSection('settings');
        $this->companyEdit->getCompanyForm()->fill($companyPaymentFixture);
        $this->companyEdit->getCompanyForm()->openSection('company_credit');
        $this->companyEdit->getCompanyForm()->fill($companyCreditFixture);
        $this->companyEdit->getFormPageActions()->save();
        $this->loginCustomer($customer);
        $this->addToCart($products);
        $orderId = $this->placeOrder($checkout);

        if (isset($amounts['reimburse'])) {
            $this->companyIndex->open();
            $this->companyIndex->getGrid()->searchAndOpen($filter);
            $this->companyEdit->getCompanyCreditFormActions()->reimburseBalance();
            $this->companyEdit->getModalReimburseBalance()->setAmount($amounts['reimburse']);
            $this->companyEdit->getModalReimburseBalance()->setComment(time());
            $this->companyEdit->getModalReimburseBalance()->reimburse();
            $this->companyEdit->getCompanyForm()->openSection('company_credit');
            $this->companyEdit->getCreditHistoryGrid()->clickReimbursedEditLink();
            $this->companyEdit->getModalReimburseBalance()->setPurchaseOrderNumber($orderId);
            $this->companyEdit->getModalReimburseBalance()->reimburse();
            $this->companyEdit->getFormPageActions()->save();
        }
        $invoiceIds = [];
        $order = null;
        if ($createInvoice) {
            $order = $this->createOrderFixture(
                $orderInjectable,
                ['id' => $orderId, 'entity_id' => ['products' => $products], 'customer_id' => ['customer' => $customer]]
            );
            $cart['data']['items']['products'] = $products;
            $invoice = $this->createInvoice($order, $products, $this->fixtureFactory->createByCode('cart', $cart));
            $invoiceIds = $invoice['ids'];
        }

        if ($cancelOrder) {
            $this->orderIndex->open();
            $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
            $this->salesOrderView->getPageActions()->cancel();
        }

        $creditMemoIds = [];
        if (!empty($productsToRefund)) {
            $creditMemoIds = $this->createCreditMemo($order, $productsToRefund);
        }

        return [
            'orderId' => $orderId,
            'company' => $company,
            'amounts' => $amounts,
            'status' => $expectedOrderStatus,
            'poNumber' => !empty($checkout['payment']['po_number']) ? $checkout['payment']['po_number'] : '',
            'paymentMethod' => $expectedPaymentMethod,
            'customer' => $customer,
            'ids' => array_merge($invoiceIds, $creditMemoIds),
            'order' => $order,
            'operations' => $operations,
            'expectedMethods' => $expectedMethods,
            'historyDataSet' => $historyDataSet,
            'commentsHistory' => $commentsHistory,
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

    /**
     * @param string $orderInjectable
     * @param array $arguments
     * @return \Magento\Sales\Test\Fixture\OrderInjectable
     */
    private function createOrderFixture($orderInjectable, array $arguments)
    {
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => $orderInjectable,
                'data' => $arguments
            ]
        );
        return $order;
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
        parent::tearDown();
    }
}
