<?php

namespace ForeverCompanies\CustomSales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\User\Model\UserFactory;
use Magento\Backend\Model\Auth\Session;

/**
 * Sales person Observer Model
 */
class SalesEventOrderToQuoteObserver implements ObserverInterface
{
    protected $userFactory;
    protected $adminSession;
    
    public function __construct(
        UserFactory $userF,
        Session $adminS
    ) {
        $this->userFactory = $userF;
        $this->adminSession = $adminS;
    }
    
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $user = $this->userFactory->create()->load($order->getData('sales_person_id'));
        $sessionUser = $this->adminSession->getUser();
        
        $quote = $observer->getData('quote');
        
        /*
            1. IF the previous order has a sales rep set:
                a. If that sales rep exists 	AND is enabled:
                    i. Set the field to the sales rep from the previous order.
                b. If that sales rep does NOT exist or is disabled:
                    i. Set it to the user making the reorder.
            2. IF the previous order does NOT have a sales rep set:
                a. Set it to the user making the reorder.
        */
        
        if (trim($order->getData('sales_person_id') != "")) {
            if ($user && $user->getIsActive() == 1) {
                $quote->setData('sales_person_id', $user->getUserId());
            } else {
                $quote->setData('sales_person_id', $sessionUser->getUserId());
            }
        } else {
            $quote->setData('sales_person_id', $sessionUser->getUserId());
        }
   
        return $this;
    }
}