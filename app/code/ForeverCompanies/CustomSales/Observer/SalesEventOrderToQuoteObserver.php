<?php

namespace ForeverCompanies\CustomSales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Sales person Observer Model
 */
class SalesEventOrderToQuoteObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getData('order');
        if ($order->getData('reordered')) {
            /** @var Quote $quote */
            $quote = $observer->getData('quote');
            $quote->setData('sales_person_id', $order->getData('sales_person_id'));
        }

        return $this;
    }
}
