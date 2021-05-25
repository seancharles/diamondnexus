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
        
        $logger->info('order get remote ip');
        $logger->info($order->getRemoteIp());
        
        
        if (trim($order->getRemoteIp()) != "")
        {
                
            
           
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
