<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\TestFramework\Helper\Bootstrap;

$giftCardCode = 'expired_giftcard_account';
// phpcs:ignore Magento2.Security.IncludeFile
require 'giftcardaccount.php';

$objectManager = Bootstrap::getObjectManager();
/** @var $model Giftcardaccount */
/** @var \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount $resourceModel */
$resourceModel = $objectManager->get(
    \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount::class
);
$resourceModel->getConnection()->update(
    $resourceModel->getMainTable(),
    [
        'date_expires' => date('Y-m-d', strtotime('-2 day')),
        'state' => Giftcardaccount::STATE_EXPIRED
    ],
    [
        'code=?' => $giftCardCode
    ]
);
