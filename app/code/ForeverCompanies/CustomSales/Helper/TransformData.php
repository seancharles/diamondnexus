<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Zend_Db_Select;

class TransformData extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $orderFactory;
    /**
     * @var string[]
     */
    protected $statusesForDelete = [
        'canceled_buyer' => 'canceled',
        'fraud' => 'canceled',
        'canceled_invalid' => 'canceled',
        'canceled_merch' => 'canceled',
        'exchanged' => 'closed',
        'exchange_pending' => 'closed',
        'holded' => 'canceled',
        'In Production' => 'processing',
        'Payment Pending' => 'canceled',
        'payment_review' => 'closed',
        'pending_payment' => 'closed',
        'Refund Complete' => 'complete',
        'returned' => 'complete',
        'return_pending' => 'complete',
        'Final QA' => 'canceled'
    ];

    /**
     * TransformData constructor.
     * @param Context $context
     * @param CollectionFactory $orderFactory+
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderFactory
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return string[]
     */
    public function getStatusesForDelete()
    {
        return $this->statusesForDelete;
    }

    /**
     * @param $old
     * @param $new
     */
    public function changeOrderStatus($old, $new)
    {
        $connection = $this->orderFactory->create()->getConnection();
        $tableName = $this->orderFactory->create()->getMainTable();
        $connection->update(
            $tableName,
            ['status' => $new],
            ['status = ?' => $old]
        );
    }

    public function deleteStatuses()
    {
        $connection = $this->orderFactory->create()->getConnection();
        $connection->delete(
            $connection->getTableName('sales_order_status'),
            ['status in (?)' => array_keys($this->getStatusesForDelete())]
        );
    }


}
