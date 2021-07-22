<?php

namespace ForeverCompanies\Salesforce\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class OrderStatusHistorySaveAfter implements ObserverInterface
{
    protected $queueFactory;

    protected $fields = [];

    public function __construct(
        QueueFactory  $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $comment = $observer->getEvent()->getStatusHistory();

        $queueModel = $this->queueFactory->create();
        $queueModel->addData([
            "entity_id" => (int) $comment->getParentId(),
            "entity_type" => "order_status_history",
            "created_at" => date("Y-m-d h:i:s")
        ]);
        $queueModel->save();
    }
}
