<?php

namespace ForeverCompanies\PaypalExpressDeliveryDates\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderCreate implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $orderId = $order->getId();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testingObserver.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($orderId."OrderId");

        if ($order->getPayment()->getMethod() == 'braintree_paypal') {
            # example "Fedex - Standard Saturday Delivers: Sat, Oct 16"
            $order->getShippingDescription();
        }
    }
}
