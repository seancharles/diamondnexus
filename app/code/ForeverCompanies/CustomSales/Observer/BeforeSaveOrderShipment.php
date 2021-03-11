<?php

namespace ForeverCompanies\CustomSales\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * ShipperHQ Shipper module observer
 */
class BeforeSaveOrderShipment implements ObserverInterface
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * BeforeSaveOrderShipment constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Update saved shipping methods available for ShipperHQ
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getData('shipment');
        $request = $this->request->getParams();
        if (isset($request['shipment']['delivery_date_actual'])) {
            $shipment->setData('delivery_date_actual', $request['shipment']['delivery_date_actual']);
        }
        if (isset($request['shipment']['final_shipping_cost'])) {
            $shipment->setData('final_shipping_cost', $request['shipment']['final_shipping_cost']);
        }
    }
}
