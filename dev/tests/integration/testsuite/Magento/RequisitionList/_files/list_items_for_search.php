<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\RequisitionList\Model\RequisitionListItem;
use Magento\TestFramework\Helper\Bootstrap;

require 'list.php';

$items = [
    [
        'sku' => 'item 1',
        'store_id' => 1,
        'qty' => 1,
        'options' => ['3'],
    ],
    [
        'sku' => 'item 2',
        'store_id' => 1,
        'qty' => 2,
        'options' => ['5'],
    ],
    [
        'sku' => 'item 3',
        'store_id' => 1,
        'qty' => 3,
        'options' => ['4'],
    ],
    [
        'sku' => 'item 4',
        'store_id' => 1,
        'qty' => 4,
        'options' => ['2'],
    ],
    [
        'sku' => 'item 5',
        'store_id' => 1,
        'qty' => 5,
        'options' => ['1'],
    ],
];

foreach ($items as $data) {
    /** @var $item RequisitionListItem */
    $item = Bootstrap::getObjectManager()->create(RequisitionListItem::class);
    $item->setRequisitionListId($list->getId());
    $item->setSku($data['sku']);
    $item->setStoreId($data['store_id']);
    $item->setQty($data['qty']);
    $item->setOptions($data['options']);
    $item->save();
}
