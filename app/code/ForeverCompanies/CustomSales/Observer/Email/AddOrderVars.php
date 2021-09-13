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
        
        if (isset($deliveryDates['dispatchDate']) === true) {
              $transport['dispatch_date'] = $deliveryDates['dispatchDate'];
        }
        
        if (isset($transport['deliveryDate']) === true) {
            $transport['delivery_date'] = date("D, M j", strtotime($transport['deliveryDate']));
        }
    }
}
