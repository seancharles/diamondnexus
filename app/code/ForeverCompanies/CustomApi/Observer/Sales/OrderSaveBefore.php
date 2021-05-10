<?php

namespace ForeverCompanies\CustomApi\Observer\Sales;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;

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
        'billing_address_id',
        'shipping_address_id',
        'anticipated_shipdate',
        'delivery_date',
        'dispatch_date',
        'status_histories',
        'customer_email'
    ];

    /**
     * OrderSaveBefore constructor.
     * @param ExtOrder $extOrder
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
        $order = $observer->getEvent()->getOrder();
        
        //order is new
        if (!$order->getId()) {
            return $this;
        }
        
        // only save entry if order hasn't been imported
        if($this->registry->registry('flag_fishbowl_entry_inserted') != 1) {
            // only create fishbowl entries if the order has a payment or has no payment reuquired.
            if($order->getTotalPaid() >= 0 || $order->getTotalPaid() == 0) {
                $changes = [];
                foreach ($this->checkingFields as $key) {
                    $data = $order->getData($key);
                    if ($data !== null) {
                        if ($order->dataHasChangedFor($key)) {
                            if ($key == 'shipping_address_id') {
                                $changes[] = 'shipping_address';
                            } elseif ($key == 'billing_address_id') {
                                $changes[] = 'billing_address';
                            } else {
                                $changes[] = $key;
                            }
                        }
                    }
                }
                if (count($changes) > 0) {
                    // flag the order for changes in session
                    $this->registry->register('flag_fishbowl_entry_inserted', 1);
                    $this->extOrder->createNewExtSalesOrder((int)$order->getId(), $changes);
                }
            }
        }
        
        return $this;
    }
}
