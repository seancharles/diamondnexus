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
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\CustomerBalance\Model\Balance\HistoryFactory;

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
        BalanceFactory $customerBalanceFactory,
        HistoryFactory $customerBalanceHistoryFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->extOrderHelper = $extOrderHelper;
        $this->customerBalanceFactory = $customerBalanceFactory;
        $this->customerBalanceHistoryFactory = $customerBalanceHistoryFactory;
        
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
        
        // get payment method
        $paymentMethod = (int)$information[Constant::PAYMENT_METHOD_DATA];
        
        if (isset($information[Constant::OPTION_TOTAL_DATA]) == true && $information[Constant::OPTION_TOTAL_DATA] == Constant::MULTIPAY_TOTAL_AMOUNT) {
            if (isset($information[Constant::AMOUNT_DUE_DATA])) {
                $amount = round($information[Constant::AMOUNT_DUE_DATA],2);
                $change = 0;
            }
        }

        if (isset($information[Constant::OPTION_TOTAL_DATA]) == true && $information[Constant::OPTION_TOTAL_DATA] == Constant::MULTIPAY_PARTIAL_AMOUNT) {
            $amount = round($information[Constant::OPTION_PARTIAL_DATA],2);
            if (isset($information[Constant::CHANGE_DUE_DATA])) {
                $change = $information[Constant::CHANGE_DUE_DATA];
            }
        }
        
        // What is this here for?
        //$template = $this->emailSender->mappingTemplate('- new order');
        //$this->emailSender->sendEmail($template, $order->getCustomerEmail(), ['order' => $order]);
        
        $this->emailSender->sendEmail('Order Update', $order->getCustomerEmail(), ['order' => $order]);
            
        $tendered = 0;
        if (isset($information[Constant::CASH_TENDERED_DATA])) {
            $tendered = round($information[Constant::CASH_TENDERED_DATA],2);
        }
        $transaction = $this->transactionFactory->create();
        $transaction->setData(
            [
                'order_id' => $orderId,
                'transaction_type' => Constant::MULTIPAY_SALE_ACTION,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'tendered' => $tendered,
                'change' => $change,
                'transaction_timestamp' => time(),
            ]
        );
        try {
            // store credit updates the grand total on the order and balance amount applied
            if($paymentMethod == Constant::MULTIPAY_STORE_CREDIT_METHOD) {
                $newGrandTotal = $order->getGrandTotal() - round($amount,2);
                $newTotalDue = $order->getToalDue() - round($amount,2);
                $newCustomerBalanceAmount = $order->getCustomerBalanceAmount() + round($amount,2);
                $order->setGrandTotal($newGrandTotal);
                $order->setTotalDue($newTotalDue);
                $order->setCustomerBalanceAmount($newCustomerBalanceAmount);
                
                $customerBalance = $this->customerBalanceFactory->create();
                $customerBalance
                    ->setCustomerId($order->getCustomerId())->loadByCustomer()
                    ->setAmountDelta(-$amount)
                    ->setOrder($order)
                    ->setHistoryAction(\Magento\CustomerBalance\Model\Balance\History::ACTION_USED)
                    ->save();
                
                $this->extOrderHelper->createNewExtSalesOrder((int)$orderId, ['payment']);
                
            } else {
                if ($order->getTotalPaid() !== null) {
                    $order->setTotalPaid($order->getTotalPaid() + $amount);
                    $this->extOrderHelper->createNewExtSalesOrder((int)$orderId, ['payment']);
                } else {
                    // Not sure why this else exists? PB
                    $order->setTotalPaid($amount);
                }
            }
            $this->save($transaction);
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
