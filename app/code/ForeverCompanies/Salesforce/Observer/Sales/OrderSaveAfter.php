<?php

namespace ForeverCompanies\Salesforce\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class OrderSaveAfter implements ObserverInterface
{
    protected $queueFactory;

    protected $fields = [
        'status',
        'billing_address_id',
        'shipping_address_id',
        'anticipated_shipdate',
        'delivery_date',
        'dispatch_date',
        'status_histories',
        'customer_email'
    ];

    public function __construct(
        QueueFactory  $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        $queueModel = $this->queueFactory->create();
        $queueModel->addData([
            "entity_id" => (int) $order->getId(),
            "entity_type" => "order",
            "created_at" => date("Y-m-d h:i:s")
        ]);
        $queueModel->save();
    }
}
