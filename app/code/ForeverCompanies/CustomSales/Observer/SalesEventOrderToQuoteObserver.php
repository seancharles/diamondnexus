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
       // if (1==0 && $order->getData('reordered')) {
       
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/reordertest.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        
        $logger->info('sales event order to quote');
        $logger->info('order get remote ip');
        $logger->info($order->getRemoteIp());
        
        $logger->info('order sales person id');
        $logger->info($order->getData('sales_person_id'));
        
        if (1==1 || trim($order->getRemoteIp()) != "")
        {
                
            /*
             
             1. IF the previous order has a sales rep set:
	a. If that sales rep exists 	AND is enabled:
		i. Set the field to the sales rep from the previous order.
	b. If that sales rep does NOT exist or is disabled:
		i. Set it to the user making the reorder.

2. IF the previous order does NOT have a sales rep set:
	a. Set it to the user making the reorder.
             
             */
            
           
            $logger->info('order data reordered');
            $logger->info($order->getData('reordered'));
            
            $logger->info('order sales person id');
            $logger->info($order->getData('sales_person_id'));
            
            /** @var Quote $quote */
            $quote = $observer->getData('quote');
            $quote->setData('sales_person_id', $order->getData('sales_person_id'));
            
            $logger->info('quote sales person id');
            $logger->info($quote->getData('sales_person_id'));
            
            $logger->info('order data keys');
            $logger->info(array_keys( $order->getData() ));
            
           
       //     $quote->save();
            
        }

        return $this;
    }
}
