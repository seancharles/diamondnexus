<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);

if ($store->load('shared_catalog', 'code')->getId()) {
    $store->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require 'shared_catalog_rollback.php';
