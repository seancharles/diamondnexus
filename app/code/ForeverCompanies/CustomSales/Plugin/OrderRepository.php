<?php

namespace ForeverCompanies\CustomSales\Plugin;

use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\User\Model\ResourceModel\User;
use ShipperHQ\Shipper\Helper\CarrierGroup;

class OrderRepository
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
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        /** @var \Magento\Sales\Model\Order $order */
        if ($order->getData('is_exchange') == null || $order->getData('is_exchange') == '0') {
            return $this->addToExtensionIsExchange($order, 'no');
        }
        if ($order->getBillingAddress()->getCountryId() == 'US') {
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress !== null) {
                if ($shippingAddress->getCountryId() !== 'US') {
                    return $this->addToExtensionIsExchange($order, 'yes');
                }
            }
            return $this->addToExtensionIsExchange($order, 'no');
        }
        return $this->addToExtensionIsExchange($order, 'yes');
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

    /**
     * @param $order
     * @param $value
     * @return OrderInterface
     */
    protected function addToExtensionIsExchange($order, $value)
    {
        /** @var OrderExtension $extensionAttributes */
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        $extensionAttributes->setFbCustomFieldId22($value);
        $order->setExtensionAttributes($extensionAttributes);
        return $order;
    }
}
