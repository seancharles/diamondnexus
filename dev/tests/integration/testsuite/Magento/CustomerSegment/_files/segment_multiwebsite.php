<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require INTEGRATION_TESTS_DIR . '/testsuite/Magento/Store/_files/core_second_third_fixturestore.php';
require INTEGRATION_TESTS_DIR . '/testsuite/Magento/Customer/_files/customer.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Store\Model\Website $mainWebsite */
/** @var \Magento\Store\Model\Website $secondWebsite */
$mainWebsite = $objectManager->create(\Magento\Store\Model\Website::class)->load('base');
$secondWebsite = $objectManager->create(\Magento\Store\Model\Website::class)->load('secondwebsite');

if (!isset($customer)) {
    /** @var \Magento\Customer\Model\Customer $customerModel */
    $customerModel = $objectManager->create(\Magento\Customer\Model\Customer::class);
    $customer = $customerModel->loadByEmail('customer@example.com');
}

/** @var $segmentFactory \Magento\CustomerSegment\Model\SegmentFactory */
$segmentFactory = $objectManager->create(\Magento\CustomerSegment\Model\SegmentFactory::class);

$data = [
    'name'          => 'Customer Segment Multi-Website',
    'website_ids'   => [$mainWebsite->getId(), $secondWebsite->getId()],
    'is_active'     => '1',
    'apply_to'      => \Magento\CustomerSegment\Model\Segment::APPLY_TO_VISITORS_AND_REGISTERED,
];

/** @var $segment \Magento\CustomerSegment\Model\Segment */
$segment = $segmentFactory->create();
$segment->loadPost($data);
$segment->save();

$conditions = [
    1 => [
        'type' => \Magento\CustomerSegment\Model\Segment\Condition\Combine\Root::class,
        'aggregator' => 'any',
        'value' => '1',
        'new_child' => '',
    ],
    '1--1' => [
        'type' => \Magento\CustomerSegment\Model\Segment\Condition\Customer\Attributes::class,
        'attribute' => 'email',
        'operator' => '==',
        'value' => $customer->getEmail(),
    ]
];
$data['segment_id'] = $segment->getSegmentId();
$data['conditions'] = $conditions;

$segment->loadPost($data);
$segment->save();
$segment->matchCustomers();
