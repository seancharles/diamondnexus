<?php

namespace ForeverCompanies\CustomApi\Observer\Sales;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Save original order status.
 */
class OrderSaveBefore implements ObserverInterface
{
    /**
     * @var ExtOrder
     */
    protected $extOrder;

    /**
     * @var string[]
     */
    protected $checkingFields = [
        'status',
        'shipping_address_id',
        'anticipated_shipdate',
        'delivery_date',
        'status_histories'
    ];

    /**
     * OrderSaveBefore constructor.
     * @param ExtOrder $extOrder
     */
    public function __construct(
        ExtOrder $extOrder
    ) {
        $this->extOrder = $extOrder;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        //order is new
        if (!$order->getId()) {
            return $this;
        }
        $changes = [];
        foreach ($this->checkingFields as $key) {
            $data = $order->getData($key);
            if ($data !== null) {
                if ($order->dataHasChangedFor($key)) {
                    $changes[] = $key;
                }
            }
        }
        if (count($changes) > 0) {
            $this->extOrder->createNewExtSalesOrder((int)$order->getId(), $changes);
        }
        return $this;
    }
}
