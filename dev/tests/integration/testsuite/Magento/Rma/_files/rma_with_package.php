<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Model\Shipping;
use Magento\TestFramework\Helper\Bootstrap;

include __DIR__ . '/../../../Magento/Rma/_files/rma.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Json $json */
$json = $objectManager->get(Json::class);
$packages = [
    [
        'params' => [
            'container' => '00',
            'weight' => '1',
            'customs_value' => '100',
            'length' => '1',
            'width' => '1',
            'height' => '1',
            'weight_units' => 'POUND',
            'dimension_units' => 'INCH',
            'content_type' => '',
            'content_type_other' => '',
            'delivery_confirmation' => '0',
        ],
        'items' => [
            [
                'qty' => '1',
                'customs_value' => '100',
                'price' => $orderProduct->getPrice(),
                'name' => $orderProduct->getName(),
                'weight' => $orderProduct->getWeight(),
                'product_id' => $orderProduct->getId(),
                'order_item_id' => $orderItem->getId(),
            ],
        ],
    ],
];

$trackingNumber->setCarrierCode('ups')
    ->setIsAdmin(Shipping::IS_ADMIN_STATUS_ADMIN_LABEL)
    ->setPackages($json->serialize($packages));
$trackRepository->save($trackingNumber);
