<?php

namespace DiamondNexus\Multipay\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TransactionInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const TRANSACTION_TYPE = 'transaction_type';
    const PAYMENT_METHOD = 'payment_method';
    const AMOUNT = 'amount';
    const TENDERED = 'tendered';
    const CHANGE = 'change';
    const CARD_TYPE = 'card_type';
    const TRANSACTION_ID = 'transaction_id';
    const TRANSACTION_TIMESTAMP = 'transaction_timestamp';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getTransactionType();

    /**
     * @param int $type
     * @return $this
     */
    public function setTransactionType($type);

    /**
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param string $method
     * @return $this
     */
    public function setPaymentMethod($method);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * @return float
     */
    public function getTendered();

    /**
     * @param float $tendered
     * @return $this
     */
    public function setTendered($tendered);

    /**
     * @return float
     */
    public function getChange();

    /**
     * @param float $change
     * @return $this
     */
    public function setChange($change);

    /**
     * @return string
     */
    public function getCardType();

    /**
     * @param string $cardType
     * @return $this
     */
    public function setCardType($cardType);

    /**
     * @return string|null
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getTransactionTimestamp();

    /**
     * @param string $transactionTimestamp
     * @return $this
     */
    public function setTransactionTimestamp($transactionTimestamp);
}
