<?php

namespace DiamondNexus\Multipay\Plugin\Model;

use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
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
    protected $transactionResource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * OrderSave constructor.
     * @param Transaction $transactionResource
     */
    public function __construct(
        Transaction $transactionResource
    ) {
        $this->transactionResource = $transactionResource;
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
        $additionalInformation = $payment->getAdditionalInformation();
        $method = $additionalInformation[Constant::PAYMENT_METHOD_DATA];
        $amount = $additionalInformation[Constant::AMOUNT_DUE_DATA];

        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            switch ($method) {
                case Constant::MULTIPAY_CREDIT_METHOD:
                case Constant::MULTIPAY_CASH_METHOD:
                case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:
                    $this->saveMultipayTransaction($order->getId(), $additionalInformation);
                    break;
                case Constant::MULTIPAY_QUOTE_METHOD:
                    // we don't really do anything with quotes yet
                    // update the amount paid on the order
                    // set the order status to quote
                    break;
            }
        }
        return $order;
    }

    /**
     * @param $orderId
     * @param $additionalInformation
     * @throws LocalizedException
     */
    protected function saveMultipayTransaction($orderId, $additionalInformation)
    {
        $otherTransactions = $this->transactionResource->getAllTransactionsByOrderId($orderId);
        if ($otherTransactions !== null && count($otherTransactions) > 0) {
            return;
        }
        $this->transactionResource->createNewTransaction($orderId, $additionalInformation);
    }
}
