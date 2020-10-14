<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model\ResourceModel;

use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

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

    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        Logger $logger,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
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
     * @param int|string $orderId
     * @param array $information
     */
    public function createNewTransaction($orderId, $information)
    {
        $transaction = $this->transactionFactory->create();
        $transaction->setData(
            [
            'order_id' => $orderId,
            'transaction_type' => $information[Constant::PAYMENT_METHOD_DATA],
            'payment_method' => $information[Constant::OPTION_TOTAL_DATA],
            'amount' => $information[Constant::OPTION_PARTIAL_DATA],
            'tendered' => $information[Constant::CASH_TENDERED_DATA],
            'change' => $information[Constant::CHANGE_DUE_DATA],
            'transaction_timestamp' => time(),
            ]
        );
        try {
            $this->save($transaction);
        } catch (AlreadyExistsException $e) {
            $this->logger->error($e->getMessage());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
