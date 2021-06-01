<?php

namespace DiamondNexus\Multipay\Plugin\Model;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\Constant as C;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\ResourceModel\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use PayPal\Braintree\Api\Data\TransactionDetailDataInterfaceFactory;
use PayPal\Braintree\Gateway\Response\PaymentDetailsHandler;
use PayPal\Braintree\Model\TransactionDetail;
use ForeverCompanies\CustomSales\Helper\Shipdate;

class OrderSave
{
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Transaction
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var State
     */
    protected $state;

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
     * @var \Magento\Framework\DB\Transaction
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
     * @var Shipdate
     */
    protected $shipdateHelper;

    /**
     * OrderSave constructor.
     * @param Transaction $resource
     * @param Data $helper
     * @param EmailSender $emailSender
     * @param State $state
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     * @param \PayPal\Braintree\Model\ResourceModel\TransactionDetail $braintreeResource
     * @param Invoice $resourceInvoice
     * @param InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        Transaction $resource,
        Data $helper,
        EmailSender $emailSender,
        State $state,
        TransactionDetailDataInterfaceFactory $transactionDetailFactory,
        \PayPal\Braintree\Model\ResourceModel\TransactionDetail $braintreeResource,
        Invoice $resourceInvoice,
        InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        InvoiceSender $invoiceSender,
        Shipdate $shipdateHelper
    ) {
        $this->transactionDetailFactory = $transactionDetailFactory;
        $this->braintreeResource = $braintreeResource;
        $this->invoiceService = $invoiceService;
        $this->resourceInvoice = $resourceInvoice;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->emailSender = $emailSender;
        $this->state = $state;
        $this->shipdateHelper = $shipdateHelper;
    }

    /**
     * Save order tax
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethod();
        $info = $payment->getAdditionalInformation();
        if (!isset($info[Constant::PAYMENT_METHOD_DATA])) {
            return $order;
        }
        $method = $info[Constant::PAYMENT_METHOD_DATA];

        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            switch ($method) {
                case Constant::MULTIPAY_CREDIT_METHOD:
                case Constant::MULTIPAY_CASH_METHOD:
                case Constant::MULTIPAY_STORE_CREDIT_METHOD:
                case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:
                case Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD:
                case Constant::MULTIPAY_PROGRESSIVE_OFFLINE_METHOD:
                    $this->saveMultipayTransaction($order, $info);
                    break;
                case Constant::MULTIPAY_QUOTE_METHOD:
                    $this->emailSender->sendEmail('new quote', $order->getCustomerEmail(), ['order' => $order]);
                    break;
            }
        }
        $this->invoiceProcessor($order);
        return $order;
    }

    /**
     * Save order tax
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return void
     * @throws ValidatorException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        if ($order->getStatus() == Order::STATE_CANCELED) {
            return;
        }
        
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethod();
        $info = $payment->getAdditionalInformation();
        
        if (!isset($info[Constant::PAYMENT_METHOD_DATA])) {
            return;
        }
        
        $multipayMethod = $info[Constant::PAYMENT_METHOD_DATA];
        
        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            if($multipayMethod != Constant::MULTIPAY_QUOTE_METHOD) {
                if (!isset($info[Constant::OPTION_TOTAL_DATA]) || $info[Constant::OPTION_TOTAL_DATA] == null) {
                    throw new ValidatorException(__('You need choose Amount option - total or partial '));
                }
            }else if($multipayMethod == Constant::MULTIPAY_CREDIT_METHOD) {
                if ($this->state->getAreaCode() !== Area::AREA_ADMINHTML) {
                    $result = $this->helper->sendToBraintree($order);
                    if ($result instanceof Error) {
                        throw new ValidatorException(__('Credit card failed verification'));
                    }
                }
            }else if ($multipayMethod == Constant::MULTIPAY_QUOTE_METHOD) {
                $order->setState('quote')->setStatus('quote');
            }

            if ($multipayMethod != Constant::MULTIPAY_QUOTE_METHOD) {
                if (
                    (isset($info[Constant::OPTION_TOTAL_DATA]) == true && $info[Constant::OPTION_TOTAL_DATA] == 1)
                    ||
                    ($order->getGrandTotal() == $order->getTotalPaid())
                    ||
                    $order->getTotalDue() == 0
                ) {
                    if(
                        isset($info[Constant::PAID_IN_FULL_FLAG]) === false
                        ||
                        (isset($info[Constant::PAID_IN_FULL_FLAG]) === true && $info[Constant::PAID_IN_FULL_FLAG] == 0)
                    ) {
                        // update to prevent further updates from making changes to delivery dates and order status
                        $info[Constant::PAID_IN_FULL_FLAG] = 1;
                        
                        $order->setAdditionalInformation($info);
                    
                        $this->shipdateHelper->updateDeliveryDates($order);
                    
                        $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
                }
                
                if($order->getTotalPaid() < $order->getGrandTotal() || $order->getTotalDue() > 0) {
                    $order->setState('pending')->setStatus('pending');
                }
            }
        }
    }

    /**
     * @param $order
     * @param $additionalInformation
     * @throws LocalizedException
     */
    protected function saveMultipayTransaction($order, $additionalInformation)
    {
        $otherTransactions = $this->resource->getAllTransactionsByOrderId($order->getId());
        if ($otherTransactions !== null && count($otherTransactions) > 0) {
            return;
        }
        $this->resource->createNewTransaction($order, $additionalInformation);
    }

    /**
     * @param OrderInterface $order
     * @throws LocalizedException
     * @throws AlreadyExistsException
     * @throws Exception
     */
    protected function invoiceProcessor(OrderInterface $order)
    {
        /** @var Order $order */
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

            if ($order->canInvoice()) {
                if($order->getGrandTotal() == 0 || $order->getTotalPaid() == $order->getGrandTotal()) {
                    
                    $sum = $info[C::OPTION_TOTAL_DATA] == '1' ? $info[C::AMOUNT_DUE_DATA] : $info[C::OPTION_PARTIAL_DATA];
                    if ($order->getTotalDue() < $sum) {
                        $shippingAmount = $order->getShippingInclTax();
                    } else {
                        $shippingAmount = 0;
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
}
