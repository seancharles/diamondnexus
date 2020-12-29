<?php

namespace ForeverCompanies\CustomSales\Plugin;

use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentExtension;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

class ShipmentRepository
{
    /**
     * @var ShipmentExtensionFactory
     */
    protected $shipmentExtension;

    /**
     * Init plugin
     *
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     */
    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory
    ) {
        $this->shipmentExtension = $shipmentExtensionFactory;
    }

    /**
     * Get Gift Wrapping
     *
     * @param ShipmentRepositoryInterface $subject
     * @param Shipment $shipment
     * @return ShipmentInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        ShipmentRepositoryInterface $subject,
        Shipment $shipment
    ) {
        /** @var ShipmentExtension $extensionAttributes */
        $extensionAttributes = $shipment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->shipmentExtension->create();
        }
        $extensionAttributes->setFinalShippingCost($shipment->getData('final_shipping_cost'));
        $extensionAttributes->setDeliveryDateActual($shipment->getData('delivery_date_actual'));
        $shipment->setExtensionAttributes($extensionAttributes);

        return $shipment;
    }

    /**
     * @param ShipmentRepositoryInterface $subject
     * @param ShipmentSearchResultInterface $shipmentSearchResult
     * @return ShipmentSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        ShipmentRepositoryInterface $subject,
        ShipmentSearchResultInterface $shipmentSearchResult
    ) {
        /** @var Shipment $entity */
        foreach ($shipmentSearchResult->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }
        return $shipmentSearchResult;
    }

    public function beforeSave(
        ShipmentRepositoryInterface $subject,
        Shipment $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes->getDeliveryDateActual() !== null) {
            $entity->setData('delivery_date_actual', $extensionAttributes->getDeliveryDateActual());
        }
        if ($extensionAttributes->getFinalShippingCost() !== null) {
            $entity->setData('final_shipping_cost', $extensionAttributes->getFinalShippingCost());
        }
    }
}
