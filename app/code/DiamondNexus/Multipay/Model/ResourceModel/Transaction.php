<?php

namespace DiamondNexus\Multipay\Model\ResourceModel;

use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\CustomerBalance\Model\Balance\HistoryFactory;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

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
    protected $customerBalanceFactory;
    protected $customerBalanceHistoryFactory;
    protected $extOrderHelper;
    protected $storeManager;

    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        Logger $logger,
        EmailSender $emailSender,
        ExtOrder $extOrderHelper,
        BalanceFactory $customerBalanceFactory,
        HistoryFactory $customerBalanceHistoryFactory,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->emailSender = $emailSender;
        $this->extOrderHelper = $extOrderHelper;
        $this->customerBalanceFactory = $customerBalanceFactory;
        $this->customerBalanceHistoryFactory = $customerBalanceHistoryFactory;
        $this->storeManager = $storeManager;
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

        if (isset($information[Constant::OPTION_TOTAL_DATA]) == true &&
            $information[Constant::OPTION_TOTAL_DATA] == Constant::MULTIPAY_TOTAL_AMOUNT) {
            if (isset($information[Constant::AMOUNT_DUE_DATA])) {
                $amount = $this->parseCurrency($information[Constant::AMOUNT_DUE_DATA]);
                $change = 0;
            }
        }

        if (isset($information[Constant::OPTION_TOTAL_DATA]) == true &&
            $information[Constant::OPTION_TOTAL_DATA] == Constant::MULTIPAY_PARTIAL_AMOUNT) {
            $amount = $this->parseCurrency($information[Constant::OPTION_PARTIAL_DATA]);
            if (isset($information[Constant::CHANGE_DUE_DATA])) {
                $change = $this->parseCurrency($information[Constant::CHANGE_DUE_DATA]);
            }
        }
        
        // What is this here for?
        //$template = $this->emailSender->mappingTemplate('- new order');
        //$this->emailSender->sendEmail($template, $order->getCustomerEmail(), ['order' => $order]);
        
        $this->emailSender->sendEmail('Order Update', $order->getCustomerEmail(), ['order' => $order]);
            
        $tendered = 0;
        if (isset($information[Constant::CASH_TENDERED_DATA])) {
            $tendered = $this->parseCurrency($information[Constant::CASH_TENDERED_DATA]);
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
            if ($paymentMethod == Constant::MULTIPAY_STORE_CREDIT_METHOD) {
                $newGrandTotal = $order->getGrandTotal() - $amount;
                $newTotalDue = $order->getToalDue() - $amount;
                $newCustomerBalanceAmount = $order->getCustomerBalanceAmount() + $amount;
                $order->setGrandTotal($newGrandTotal);
                $order->setTotalDue($newTotalDue);
                $order->setCustomerBalanceAmount($newCustomerBalanceAmount);

                $storeId = $order->getStoreId();
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

                $customerBalance = $this->customerBalanceFactory->create();
                $customerBalance
                    ->setWebsiteId($websiteId)
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
                $paidPart += round($transaction['amount'], 2);
                if ($transaction['amount'] == 0) {
                    $paidPart += round($transaction['tendered'], 2);
                }
            }
        } catch (LocalizedException $e) {
            return 0;
        }
        return $paidPart;
    }

    public function parseCurrency($amount = 0)
    {
        $return = (double) filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (abs($return) > 0) {
            return bcdiv($return, 1, 2);
        } else {
            return 0;
        }
    }
}
