<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $model \Magento\GiftCardAccount\Model\Giftcardaccount */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\GiftCardAccount\Model\Giftcardaccount::class
);
$model->loadByCode('giftcardaccount_fixture');
$model->setDateExpires(date('Y-m-d', strtotime('-1 day')));
/** @var \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount $resourceModel */
$resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount::class
);
$resourceModel->load($model, 'giftcardaccount_fixture');
