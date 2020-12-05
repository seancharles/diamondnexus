<?php

namespace DiamondNexus\Multipay\Plugin\Model;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

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
     * OrderSave constructor.
     * @param Transaction $resource
     * @param Data $helper
     * @param EmailSender $emailSender
     */
    public function __construct(
        Transaction $resource,
        Data $helper,
        EmailSender $emailSender
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
        $this->emailSender = $emailSender;
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
        $information = $payment->getAdditionalInformation();
        if (!isset($information[Constant::PAYMENT_METHOD_DATA])) {
            return $order;
        }
        $method = $information[Constant::PAYMENT_METHOD_DATA];

        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            switch ($method) {
                case Constant::MULTIPAY_CREDIT_METHOD:
                case Constant::MULTIPAY_CASH_METHOD:
                case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:
                    $this->saveMultipayTransaction($order, $information);
                    break;
                case Constant::MULTIPAY_QUOTE_METHOD:
                    $this->emailSender->sendEmail('new quote', $order->getCustomerEmail(), ['order' => $order]);
                    break;
            }
        }
        return $order;
    }

    /**
     * Save order tax
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return void
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        if ($order->getState() == 'quote' && $order->getStatus() == 'quote') {
            $requiredQuote = true;
        }
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethod();
        $information = $payment->getAdditionalInformation();
        if (!isset($information[Constant::PAYMENT_METHOD_DATA])) {
            return;
        }
        $method = $information[Constant::PAYMENT_METHOD_DATA];
        if ($methodInstance === Constant::MULTIPAY_METHOD && $method != Constant::MULTIPAY_QUOTE_METHOD) {
            if ($information[Constant::OPTION_TOTAL_DATA] == null) {
                throw new ValidatorException(__('You need choose Amount option - total or partial '));
            }
        }
        if ($methodInstance === Constant::MULTIPAY_METHOD && $method == Constant::MULTIPAY_CREDIT_METHOD) {
            $result = $this->helper->sendToBraintree($order);
            if ($result instanceof Error) {
                throw new ValidatorException(__('Credit card failed verification'));
            }
        }
        if (isset($information[Constant::OPTION_TOTAL_DATA])) {
            if ($methodInstance === Constant::MULTIPAY_METHOD && $information[Constant::OPTION_TOTAL_DATA] == 1) {
                $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
            }
            if ($methodInstance === Constant::MULTIPAY_METHOD && $information[Constant::OPTION_TOTAL_DATA] == 2) {
                $order->setState('pending')->setStatus('pending');
            }
        }
        if ($methodInstance == Constant::MULTIPAY_METHOD && $method == Constant::MULTIPAY_QUOTE_METHOD) {
            $order->setState('quote')->setStatus('quote');
        }
        if (isset($requiredQuote) && $requiredQuote == true) {
            $order->setStatus('quote');
            $order->setState('quote');
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
}
