<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

include __DIR__ . '/../../../Magento/Sales/_files/order_configurable_product.php';

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->loadByIncrementId('100000001');

foreach ($order->getItems() as $orderItem) {
    if ($orderItem->getProductType() == Configurable::TYPE_CODE) {
        $orderItem->setQtyShipped($qtyOrdered);
        $orderItem->setProductOptions(['simple_sku' => 'simple_10']);

        /** @var \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemsRepository */
        $orderItemsRepository = $objectManager->create(\Magento\Sales\Api\OrderItemRepositoryInterface::class);
        $orderItemsRepository->save($orderItem);
    }
}
