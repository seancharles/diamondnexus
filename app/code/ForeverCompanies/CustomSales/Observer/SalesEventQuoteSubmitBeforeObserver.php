<?php

namespace ForeverCompanies\CustomSales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Sales person Observer Model
 */
class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * Set sales_person_id to order from quote
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $quote = $observer->getData('quote');
        $order->setData('sales_person_id', $quote->getData('sales_person_id'));
        return $this;
    }
}
