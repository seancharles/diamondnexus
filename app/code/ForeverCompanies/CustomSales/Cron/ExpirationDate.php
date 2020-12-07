<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Cron;

use Exception;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\SalesArchive\Model\ArchivalList;
use Magento\SalesArchive\Model\ResourceModel\Archive;

class ExpirationDate
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    protected $commentFactory;

    /**
     * @var Archive
     */
    protected $archive;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderStatusHistoryInterfaceFactory $commentFactory
     * @param Archive $archive
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        OrderStatusHistoryInterfaceFactory $commentFactory,
        Archive $archive
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->commentFactory = $commentFactory;
        $this->archive = $archive;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        /** @var Collection $collection */
        $date = date('Y-m-d', strtotime('now'));
        $comment = $this->commentFactory->create()
            ->setComment('Quote automatically expired on ' . $date)
            ->setStatus(Order::STATE_CANCELED);
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('status', ['eq' => 'quote'])
            ->addFieldToFilter('quote_expiration_date', ['lteq' => $date]);
        $allIds = $collection->getAllIds();
        if (count($allIds) > 0) {
            foreach ($allIds as $orderId) {
                $this->orderManagement->cancel($orderId);
                $this->orderManagement->addComment($orderId, $comment);
            }
            $this->archive->moveToArchive(
                ArchivalList::ORDER,
                Order::ENTITY_ID,
                $allIds
            );
        }
    }
}
