<?php

namespace ForeverCompanies\CustomApi\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderItemLooseStone
{
    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * Init plugin
     *
     * @param OrderItemExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderItemExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Get Gift Wrapping
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultEntity
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultEntity
    ) {

        foreach ($resultEntity->getItems() as $orderItem) {
            $this->processOrderItemGiftWrapping($orderItem);
        }

        return $resultEntity;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $resultEntity
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $resultEntity
    ) {
        foreach ($resultEntity->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }
        return $resultEntity;
    }

    /**
     * @param OrderItemInterface $resultEntity
     */
    private function processOrderItemGiftWrapping(OrderItemInterface $resultEntity)
    {
        /** @var OrderItemExtension $extensionAttributes */
        $extensionAttributes = $resultEntity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        $extensionAttributes->setFlagLooseStone($resultEntity->getData('flag_loose_stone'));
        $resultEntity->setExtensionAttributes($extensionAttributes);
    }
}
