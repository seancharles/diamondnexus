<?php

namespace ForeverCompanies\CustomApi\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\User\Model\ResourceModel\User;
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
     * @var User
     */
    protected $userResource;

    /**
     * Init plugin
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param CarrierGroup $carrierGroup
     * @param User $userResource
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        CarrierGroup $carrierGroup,
        User $userResource
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->carrierGroup = $carrierGroup;
        $this->userResource = $userResource;
    }

    /**
     * Get Gift Wrapping
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
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
        if ($order->getData('sales_person_id') !== null && $order->getData('sales_person_id') != 0) {
            $salesPersonSql = $this->userResource->getConnection()
                ->select()->from($this->userResource->getMainTable(), ['username'])
                ->where('user_id = ?', $order->getData('sales_person_id'));
            $salesPersonResult = $this->userResource->getConnection()->fetchRow($salesPersonSql);
            $extensionAttributes->setSalesPersonUsername($salesPersonResult['username']);
        }
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

    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes->getFlagFishbowlImport() !== null) {
            $entity->setData('flag_fishbowl_import', $extensionAttributes->getFlagFishbowlImport());
        }
        if ($extensionAttributes->getAnticipatedShipdate() !== null) {
            $entity->setData('anticipated_shipdate', $extensionAttributes->getAnticipatedShipdate());
        }
        if ($extensionAttributes->getDeliveryDate() !== null) {
            $entity->setData('delivery_date', $extensionAttributes->getDeliveryDate());
        }
        if ($extensionAttributes->getDispatchDate() !== null) {
            $entity->setData('dispatch_date', $extensionAttributes->getDeliveryDate());
        }
        if ($extensionAttributes->getShippingMethod() !== null) {
            $entity->setData('shipping_method', $extensionAttributes->getShippingMethod());
        }
        if ($extensionAttributes->getSalesPersonId() !== null) {
            $entity->setData('sales_person_id', $extensionAttributes->getSalesPersonId());
        }
    }
}
