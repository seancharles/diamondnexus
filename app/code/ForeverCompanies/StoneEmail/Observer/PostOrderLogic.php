<?php

namespace ForeverCompanies\StoneEmail\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class PostOrderLogic implements ObserverInterface
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderIds = $observer->getEvent()->getOrderIds();
        
        
        // BMLGD-80156
        
        echo 'the order increment id is ' . $order->getIncrementId() . '<br />';
        echo 'the order id is ' . $order->getId() . '<br />';
        echo '<pre>';
        var_dump("order ids", $orderIds);
        die;
        
        $this->logger->debug('zzzzzzz the order increment id is ' . $order->getIncrementId());
        $this->logger->debug('the order id is ' . $order->getId());
        $this->logger->debug("order ids");
        $this->logger->debug($orderIds);
        $this->logger->debug("");
        
       
        
        
    }
}