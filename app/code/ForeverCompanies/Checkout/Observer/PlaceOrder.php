<?php

namespace ForeverCompanies\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
    
class PlaceOrder implements ObserverInterface
{
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        
        $orderId = $observer->getEvent()->getOrderIds()[0];

        $order = $this->orderRepository->get($orderId);\
        
        // set the order id for the customer in their session
        $this->_checkoutSession->setGuestOrderId($order->getId());
    }
}
