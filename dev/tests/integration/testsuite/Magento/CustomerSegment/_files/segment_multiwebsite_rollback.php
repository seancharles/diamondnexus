<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $segment \Magento\CustomerSegment\Model\Segment */
$segment = $objectManager->create(\Magento\CustomerSegment\Model\Segment::class);

$segment->load('Customer Segment Multi-Website', 'name');
if ($segment->getId()) {
    $segment->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require INTEGRATION_TESTS_DIR . '/testsuite/Magento/Customer/_files/customer_rollback.php';
require INTEGRATION_TESTS_DIR . '/testsuite/Magento/Store/_files/core_second_third_fixturestore_rollback.php';
