<?php

namespace ForeverCompanies\Salesforce\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class OrderAddressSaveAfter implements ObserverInterface
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
        $orderAddress = $observer->getData('address');

        $queueModel = $this->queueFactory->create();
        $queueModel->addData([
            "entity_id" => (int) $orderAddress->getParentId(),
            "entity_type" => "order_address",
            "created_at" => date("Y-m-d h:i:s")
        ]);
        $queueModel->save();
    }
}
