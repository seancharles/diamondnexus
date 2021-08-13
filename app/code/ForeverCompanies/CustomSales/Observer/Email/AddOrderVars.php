<?php

namespace ForeverCompanies\CustomSales\Observer\Email;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use ForeverCompanies\CustomSales\Helper\Shipdate;

class AddOrderVars implements ObserverInterface
{
    /**
     * @var Shipdate
     */
    protected $shipdateHelper;

    public function __construct(
        Shipdate $shipdateHelper
    ) {
        $this->shipdateHelper = $shipdateHelper;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        
        $order = $transport->getOrder();
        
        $transport['dispatch_date'] = null;
        $transport['delivery_date'] = null;
        
        $deliveryDates = $this->shipdateHelper->getDeliveryDates($order);
        
        if (isset($deliveryDates['dispatch_date']) === true) {
            $transport['dispatch_date'] = $deliveryDates['dispatch_date'];
        }
        
        if (isset($deliveryDates['delivery_date']) === true) {
            $transport['delivery_date'] = $deliveryDates['delivery_date'];
        }
    }
}
