<?php
namespace ForeverCompanies\Delighted\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;
use ForeverCompanies\Delighted\Model\Person;

use Psr\Log\LoggerInterface;
use Magento\Directory\Model\RegionFactory;

class Delighted implements ObserverInterface
{
    protected $orderFactory;
    protected $person;
    protected $logger;
    protected $regionFactory;

    public function __construct(
        OrderFactory $orderF,
        Person $p,
        LoggerInterface $loggerI,
        RegionFactory $regionF
    ) {
        $this->orderFactory = $orderF;
        $this->person = $p;
        $this->regionFactory = $regionF;
        $this->logger = $loggerI;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        
        foreach ($orderIds as $orderId) {
            $order = $this->orderFactory->create()->load($orderId);
            $region = $this->regionFactory->create()->load( $order->getBillingAddress()->getRegionId() );
            
            $result = $this->person->create([
    	       'email' => $order->getCustomerEmail(),
    	       'properties' => [
    	           'Purchase Experience' => $order->getStore()->getName(),
    	           'State' => $region->getCode()
	           ]
	        ]);
        }
    }
}