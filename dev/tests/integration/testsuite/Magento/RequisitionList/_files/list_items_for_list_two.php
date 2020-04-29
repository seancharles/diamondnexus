<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\RequisitionList\Model\RequisitionListItem;
use Magento\TestFramework\Helper\Bootstrap;

$list = require 'list_two.php';

$objectManager = Bootstrap::getObjectManager();

$items = [
    [
        'sku' => 'list two item 1',
        'store_id' => 1,
        'qty' => 1,
        'options' => ['3'],
    ],
    [
        'sku' => 'list two item 2',
        'store_id' => 1,
        'qty' => 2,
        'options' => ['5'],
    ],
];

foreach ($items as $data) {
    /** @var $item RequisitionListItem */
    $item = $objectManager->create(RequisitionListItem::class);
    $item->setRequisitionListId($list->getId());
    $item->setSku($data['sku']);
    $item->setStoreId($data['store_id']);
    $item->setQty($data['qty']);
    $item->setOptions($data['options']);
    $item->save();
}
