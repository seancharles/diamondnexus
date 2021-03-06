<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Helper;

use ForeverCompanies\CustomApi\Api\Data\ExtSalesOrderUpdateInterface;
use ForeverCompanies\CustomApi\Model\ExtSalesOrderUpdateFactory;
use ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate as ExtResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;

class ExtOrder extends AbstractHelper
{
    /**
     * @var ExtSalesOrderUpdateFactory
     */
    protected $extSalesOrderUpdateFactory;

    /**
     * @var ExtResource
     */
    protected $extResource;

    /**
     * ExtOrder constructor.
     * @param Context $context
     * @param ExtSalesOrderUpdateFactory $extSalesOrderUpdateFactory
     * @param ExtResource $extResource
     */
    public function __construct(
        Context $context,
        ExtSalesOrderUpdateFactory $extSalesOrderUpdateFactory,
        ExtResource $extResource
    ) {
        parent::__construct($context);
        $this->extSalesOrderUpdateFactory = $extSalesOrderUpdateFactory;
        $this->extResource = $extResource;
    }

    /**
     * @param int $orderId
     * @param array|string $data
     * @param int $flag
     */
    public function createNewExtSalesOrder(int $orderId, $data, $flag = 0)
    {
        $extOrder = $this->extSalesOrderUpdateFactory->create();
        $changesText = is_array($data) ? implode(', ', $data) : $data;
        $extOrder->setOrderId($orderId);
        $extOrder->setUpdatedFields($changesText);
        $extOrder->setFlag((int)$flag);
        try {
            $this->extResource->save($extOrder);
        } catch (AlreadyExistsException $e) {
            $this->_logger->error('Can\'t create new ExtOrder - ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->error('Something went wrong when order updates - ' . $e->getMessage());
        }
    }

    /**
     * @param int $entityId
     * @param bool $flag
     * @return string
     */
    public function updateExtSalesOrder(int $entityId, bool $flag)
    {
        $connection = $this->extResource->getConnection();
        try {
            $mainTable = $this->extResource->getMainTable();
            $idFieldName = $this->extResource->getIdFieldName();
            $select = $connection->select()->from($mainTable)
                ->where(ExtSalesOrderUpdateInterface::ENTITY_ID . ' = ?', $entityId)
                ->order($idFieldName . ' desc')
                ->limit(1);
            $row = $connection->fetchRow($select);
            if (!$row) {
                return 'Can\'t find ext sales row with entity_id = ' . $entityId;
            }
            $connection->update(
                $mainTable,
                [ExtSalesOrderUpdateInterface::FLAG => (int)$flag],
                [ExtSalesOrderUpdateInterface::ENTITY_ID . ' = ?' => $entityId]
            );
            return 'Success!';
        } catch (LocalizedException $e) {
            $this->_logger->error('Can\'t update ExtOrder - ' . $e->getMessage());
            return 'Can\'t update ExtOrder - ' . $e->getMessage();
        }
    }
}
