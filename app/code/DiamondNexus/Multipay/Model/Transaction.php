<?php

namespace DiamondNexus\Multipay\Model;

use DiamondNexus\Multipay\Api\Data\TransactionInterface;
use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel implements TransactionInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Transaction::class);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionType()
    {
        return $this->getData(self::TRANSACTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionType($type)
    {
        return $this->setData(self::TRANSACTION_TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod($method)
    {
        return $this->setData(self::PAYMENT_METHOD, $method);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getTendered()
    {
        return $this->getData(self::TENDERED);
    }

    /**
     * @inheritDoc
     */
    public function setTendered($tendered)
    {
        return $this->setData(self::TENDERED, $tendered);
    }

    /**
     * @inheritDoc
     */
    public function getChange()
    {
        return $this->getData(self::CHANGE);
    }

    /**
     * @inheritDoc
     */
    public function setChange($change)
    {
        return $this->setData(self::CHANGE, $change);
    }

    /**
     * @inheritDoc
     */
    public function getCardType()
    {
        return $this->getData(self::CARD_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setCardType($cardType)
    {
        return $this->setData(self::CARD_TYPE, $cardType);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionTimestamp()
    {
        return $this->getData(self::TRANSACTION_TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionTimestamp($transactionTimestamp)
    {
        return $this->setData(self::TRANSACTION_TIMESTAMP, $transactionTimestamp);
    }
}
