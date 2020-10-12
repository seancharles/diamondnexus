<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\ExtSalesOrderUpdateManagementInterface;
use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Exception\LocalizedException;

class ExtSalesOrderUpdateManagement implements ExtSalesOrderUpdateManagementInterface
{
    /**
     * @var ResourceModel\ExtSalesOrderUpdate
     */
    protected $resourceModel;

    /**
     * @var ExtOrder
     */
    protected $helper;

    /**
     * ExtSalesOrderUpdateManagement constructor.
     * @param ResourceModel\ExtSalesOrderUpdate $resourceModel
     * @param ExtOrder $helper
     */
    public function __construct(
        \ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate $resourceModel,
        ExtOrder $helper
    ) {
        $this->resourceModel = $resourceModel;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function getExtSalesOrderUpdate(bool $flagFishbowlUpdate)
    {
        $flag = ($flagFishbowlUpdate == 'true') ? 1 : 0;
        $connection = $this->resourceModel->getConnection();
        $mainTable = $this->resourceModel->getMainTable();
        $select = $connection->select()
            ->from($mainTable, ['entity_id', 'order_id', 'updated_at', 'updated_fields', 'flag_fishbowl_update'])
            ->where('flag_fishbowl_update = ?', $flag);
        return $connection->fetchAll($select);
    }

    /**
     * {@inheritdoc}
     */
    public function postExtSalesOrderUpdate($orderId, $updatedFields, $flagFishbowlUpdate)
    {
        $this->helper->createNewExtSalesOrder($orderId, $updatedFields, $flagFishbowlUpdate);
        return 'Success';
    }

    /**
     * {@inheritdoc}
     */
    public function putExtSalesOrderUpdate(int $orderId, bool $flagFishbowlUpdate)
    {
        return $this->helper->updateExtSalesOrder($orderId, $flagFishbowlUpdate);
    }
}
