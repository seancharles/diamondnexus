<?php

namespace ForeverCompanies\CustomApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ExtSalesOrderUpdateInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const UPDATED_AT = 'updated_at';
    const UPDATED_FIELDS = 'updated_fields';
    const FLAG = 'flag_fishbowl_update';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId(int $orderId): void;

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void;

    /**
     * @return string
     */
    public function getUpdatedFields();

    /**
     * @param string $fields
     * @return void
     */
    public function setUpdatedFields(string $fields): void;

    /**
     * @return int
     */
    public function getFlag();

    /**
     * @param int $flag
     * @return void
     */
    public function setFlag(int $flag): void;
}
