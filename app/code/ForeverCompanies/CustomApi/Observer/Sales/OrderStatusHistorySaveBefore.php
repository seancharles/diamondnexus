<?php

namespace ForeverCompanies\CustomApi\Observer\Sales;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Save original order status.
 */
class OrderStatusHistorySaveBefore implements ObserverInterface
{
    /**
     * @var ExtOrder
     */
    protected $extOrder;

    protected $registry;

    /**
     * OrderSaveBefore constructor.
     * @param ExtOrder $extOrder
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        ExtOrder $extOrder,
        \Magento\Framework\Registry $registry
    ) {
        $this->extOrder = $extOrder;
        $this->registry = $registry;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $statusHistory = $observer->getEvent()->getStatusHistory();

        //order is new
        if (!$statusHistory->getOrder()->getId()) {
            return $this;
        }

        $changes = ['status_histories'];

        $this->extOrder->createNewExtSalesOrder((int)$statusHistory->getOrder()->getId(), $changes);

        return $this;
    }
}
