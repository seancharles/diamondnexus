<?php

namespace ForeverCompanies\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
    
class AddToCart implements ObserverInterface
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
        
        $setId = $this->_checkoutSession->getBundleIdentifier();

        if($setId > 0) {
            $item->setSetId($setId);
        }
    }
}
