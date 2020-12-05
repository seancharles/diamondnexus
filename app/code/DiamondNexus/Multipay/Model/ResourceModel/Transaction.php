<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model\ResourceModel;

use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\Order;

/**
 * Class Transaction
 * @package DiamondNexus\Multipay\Model\ResourceModel
 */
class Transaction extends AbstractDb
{
    /**
     * @var string
     */
    protected $mainTable = 'diamondnexus_multipay_transaction';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var ExtOrder
     */
    protected $extOrderHelper;

    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        Logger $logger,
        EmailSender $emailSender,
        ExtOrder $extOrderHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->extOrderHelper = $extOrderHelper;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init($this->mainTable, $this->primaryKey);
    }

    /**
     * @param $orderId
     * @return array
     * @throws LocalizedException
     */
    public function getAllTransactionsByOrderId($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('order_id = ?', $orderId);
        return $connection->fetchAll($select);
    }

    /**
     * @param Order $order
     * @param array $information
     */
    public function createNewTransaction(Order $order, $information)
    {
        $amount = 0;
        $change = 0;
        $orderId = $order->getId();
        if (isset($information[Constant::CHANGE_DUE_DATA])) {
            $change = $information[Constant::CHANGE_DUE_DATA];
        }
        if ((int)$information[Constant::PAYMENT_METHOD_DATA] == Constant::MULTIPAY_TOTAL_AMOUNT) {
            if (isset($information[Constant::OPTION_PARTIAL_DATA])) {
                $amount = $information[Constant::OPTION_PARTIAL_DATA];
            }
        }
        if ((int)$information[Constant::PAYMENT_METHOD_DATA] == Constant::MULTIPAY_PARTIAL_AMOUNT) {
            $amount = $information[Constant::CASH_TENDERED_DATA];
            if ($change == 0 && $amount > $information[Constant::AMOUNT_DUE_DATA]) {
                $change = $amount - $information[Constant::AMOUNT_DUE_DATA];
            }
        }
        if ((int)$information[Constant::OPTION_TOTAL_DATA] == 1) {
            $amount = $information[Constant::AMOUNT_DUE_DATA];
            $change = 0;
            $template = $this->emailSender->mappingTemplate('- new order');
            $this->emailSender->sendEmail($template, $order->getCustomerEmail(), ['order' => $order]);
        } else {
            $this->emailSender->sendEmail('Order Update', $order->getCustomerEmail(), ['order' => $order]);
        }
        $tendered = 0;
        if (isset($information[Constant::CASH_TENDERED_DATA])) {
            $tendered = $information[Constant::CASH_TENDERED_DATA];
        }
        $transaction = $this->transactionFactory->create();
        $transaction->setData(
            [
                'order_id' => $orderId,
                'transaction_type' => Constant::MULTIPAY_SALE_ACTION,
                'payment_method' => $information[Constant::PAYMENT_METHOD_DATA],
                'amount' => $amount,
                'tendered' => $tendered,
                'change' => $change,
                'transaction_timestamp' => time(),
            ]
        );
        try {
            $order->setTotalPaid($order->getTotalPaid() + $amount);
            $this->save($transaction);
            $this->extOrderHelper->createNewExtSalesOrder((int)$orderId, ['payment']);
        } catch (AlreadyExistsException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param int $orderId
     * @return float|int
     */
    public function getPaidPart($orderId)
    {
        $paidPart = 0;
        try {
            $transactions = $this->getAllTransactionsByOrderId($orderId);
            foreach ($transactions as $transaction) {
                $paidPart += (float)$transaction['amount'];
                if ($transaction['amount'] == 0) {
                    $paidPart += (float)$transaction['tendered'];
                }
            }
        } catch (LocalizedException $e) {
            return 0;
        }
        return $paidPart;
    }
}
