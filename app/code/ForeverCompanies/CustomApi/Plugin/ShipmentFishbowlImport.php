<?php

namespace ForeverCompanies\CustomApi\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentExtension;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;

class ShipmentFishbowlImport
{
    protected $shipmentExtensionFactory;

    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory
    ) {
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
    }

    public function afterGet(
        ShipmentRepositoryInterface $subject,
        ShipmentInterface $shipment
    ) {
        $extensionAttributes = $shipment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->shipmentExtensionFactory->create();
        }
        $extensionAttributes->setFlagFishbowlImport($shipment->getData('flag_fishbowl_import'));
        $shipment->setExtensionAttributes($extensionAttributes);

        return $shipment;
    }

    public function afterGetList(
        ShipmentRepositoryInterface $subject,
        ShipmentSearchResultInterface $shipmentSearchResult
    ) {
        foreach ($shipmentSearchResult->getItems() as $shipment) {
            $this->afterGet($subject, $shipment);
        }
        return $shipmentSearchResult;
    }

    public function beforeSave(
        ShipmentRepositoryInterface $subject,
        ShipmentInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes->getFlagFishbowlImport() !== null) {
            $entity->setData('flag_fishbowl_import', $extensionAttributes->getFlagFishbowlImport());
        }
    }
}
