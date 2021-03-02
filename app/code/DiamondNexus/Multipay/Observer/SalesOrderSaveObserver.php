<?php

namespace DiamondNexus\Multipay\Observer;

use DiamondNexus\Multipay\Model\Constant as C;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use PayPal\Braintree\Gateway\Response\PaymentDetailsHandler;
use PayPal\Braintree\Api\Data\TransactionDetailDataInterfaceFactory;
use Magento\Sales\Model\Order;
use PayPal\Braintree\Model\TransactionDetail;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var TransactionDetailDataInterfaceFactory
     */
    protected $transactionDetailFactory;

    /**
     * @var \PayPal\Braintree\Model\ResourceModel\TransactionDetail
     */
    protected $braintreeResource;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Invoice
     */
    protected $resourceInvoice;

    /**
     * SalesOrderPlaceObserver constructor.
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     * @param \PayPal\Braintree\Model\ResourceModel\TransactionDetail $braintreeResource
     * @param Invoice $resourceInvoice
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        TransactionDetailDataInterfaceFactory $transactionDetailFactory,
        \PayPal\Braintree\Model\ResourceModel\TransactionDetail $braintreeResource,
        Invoice $resourceInvoice,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender
    ) {
        $this->transactionDetailFactory = $transactionDetailFactory;
        $this->braintreeResource = $braintreeResource;
        $this->invoiceService = $invoiceService;
        $this->resourceInvoice = $resourceInvoice;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Save additional transaction information for braintree methods
     *
     * @param Observer $observer
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');

        if (!$order->getId()) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if (0 === strpos($paymentMethod, 'multipay')) {
            $info = $order->getPayment()->getAdditionalInformation();
            if (!empty($info[PaymentDetailsHandler::TRANSACTION_SOURCE])) {
                /** @var TransactionDetail $transactionDetail */
                $transactionDetail = $this->transactionDetailFactory->create();

                // $order-isObjectNew is always false. Workaround: ensure no entries are added if one exists already
                $this->braintreeResource->load($transactionDetail, $order->getId(), 'order_id');
                if (!$transactionDetail->getId()) {
                    $transactionDetail->setOrderId($order->getId());
                    $transactionDetail->setTransactionSource(
                        $info[PaymentDetailsHandler::TRANSACTION_SOURCE]
                    );
                    $this->braintreeResource->save($transactionDetail);
                }
            }
            if ($order->canInvoice() && $order->getStatus() !== 'quote') {
                $sum = $info[C::OPTION_TOTAL_DATA] == '1' ? $info[C::AMOUNT_DUE_DATA] : $info[C::OPTION_PARTIAL_DATA];
                if ($order->getTotalDue() < $sum) {
                    $shippingAmount = $order->getShippingInclTax();
                } else {
                    $amountWithoutShip = $order->getTotalDue() - $order->getShippingInclTax();
                    if ($amountWithoutShip < $sum) {
                        $shippingAmount = $sum - $amountWithoutShip;
                    } else {
                        $shippingAmount = 0;
                    }
                }
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setShippingAmount($shippingAmount);
                $invoice->setSubtotal($sum - $shippingAmount);
                $invoice->setBaseSubtotal($sum - $shippingAmount);
                $invoice->setGrandTotal($sum);
                $invoice->setBaseGrandTotal($sum);
                $invoice->register();
                $this->resourceInvoice->save($invoice);
                $transactionSave = $this->transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
                //Send Invoice mail to customer
                $order->addCommentToStatusHistory(
                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true);
            }
        }
    }
}
