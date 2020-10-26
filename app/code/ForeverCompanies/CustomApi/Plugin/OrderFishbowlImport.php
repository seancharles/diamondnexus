<?php

namespace ForeverCompanies\CustomApi\Plugin;

use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use ShipperHQ\Shipper\Helper\CarrierGroup;
use ShipperHQ\Shipper\Model\Order\GridDetail;

class OrderFishbowlImport
{
    /**
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * @var CarrierGroup
     */
    protected $carrierGroup;

    /**
     * Init plugin
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param CarrierGroup $carrierGroup
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        CarrierGroup $carrierGroup
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->carrierGroup = $carrierGroup;
    }

    /**
     * Get Gift Wrapping
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        /** @var OrderExtension $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        /** @var GridDetail $carrierData */
        $carrierData = $this->carrierGroup->loadOrderGridDetailByOrderId($order->getEntityId())->getFirstItem();
        $extensionAttributes->setFlagFishbowlImport($order->getData('flag_fishbowl_import'));
        $extensionAttributes->setShippingMethod($order->getShippingMethod());
        $extensionAttributes->setAnticipatedShipdate($carrierData->getDispatchDate());
        $extensionAttributes->setDeliveryDate($carrierData->getDeliveryDate());
        $extensionAttributes->setSalesPersonId($order->getData('sales_person_id'));
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $orderSearchResult
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $orderSearchResult
    ) {
        /** @var OrderInterface $entity */
        foreach ($orderSearchResult->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $orderSearchResult;
    }
}
