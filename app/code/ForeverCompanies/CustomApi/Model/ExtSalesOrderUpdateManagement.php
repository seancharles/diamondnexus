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
    public function getExtSalesOrderUpdate($flagFishbowlUpdate)
    {
        if ($flagFishbowlUpdate !== 'true' && $flagFishbowlUpdate !== 'false') {
            return 'Write param flag_fishbowl_update "true" or "false", please';
        }
        $flag = ($flagFishbowlUpdate == 'true') ? 1 : 0;
        $connection = $this->resourceModel->getConnection();
        $mainTable = $this->resourceModel->getMainTable();
        $select = $connection->select()
            ->from($mainTable, ['order_id', 'updated_at', 'updated_fields', 'flag_fishbowl_update'])
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
}
