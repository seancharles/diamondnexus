<?php

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\Data\ExtSalesOrderUpdateInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;

class ExtSalesOrderUpdate extends AbstractModel implements IdentityInterface, ExtSalesOrderUpdateInterface
{
    const CACHE_TAG = 'forevercompanies_customapi_extsalesorderupdate';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\ExtSalesOrderUpdate::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId(int $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->getData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedFields()
    {
        return $this->getData(self::UPDATED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedFields(string $fields): void
    {
        $this->setData(self::UPDATED_FIELDS, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function getFlag()
    {
        return $this->getData(self::FLAG);
    }

    /**
     * {@inheritdoc}
     */
    public function setFlag(int $flag): void
    {
        $this->setData(self::FLAG, $flag);
    }
}
