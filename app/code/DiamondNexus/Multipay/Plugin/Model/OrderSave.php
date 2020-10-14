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
    protected $resource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * OrderSave constructor.
     * @param Transaction $resource
     */
    public function __construct(
        Transaction $resource
    )
    {
        $this->resource = $resource;
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
    )
    {
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethod();
        $information = $payment->getAdditionalInformation();
        $method = $information[Constant::PAYMENT_METHOD_DATA];

        if ($methodInstance === Constant::MULTIPAY_METHOD) {
            switch ($method) {
                case Constant::MULTIPAY_CREDIT_METHOD:
                case Constant::MULTIPAY_CASH_METHOD:
                case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:
                    $id = $order->getId();
                $this->saveMultipayTransaction($id, $information);
                    break;
                case Constant::MULTIPAY_QUOTE_METHOD:

                    break;
            }
        }
        return $order;
    }

    /**
     * @param $id
     * @param $additionalInformation
     * @throws LocalizedException
     */
    protected function saveMultipayTransaction($id, $additionalInformation)
    {
        $otherTransactions = $this->resource->getAllTransactionsByOrderId($id);
        if ($otherTransactions !== null && count($otherTransactions) > 0) {
            return;
        }
        $this->resource->createNewTransaction($id, $additionalInformation);
    }
}
