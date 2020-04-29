<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

include 'product_simple.php';
include 'company_with_customer_for_quote.php';

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(
    CustomerRepositoryInterface::class
);
/** @var CartManagementInterface $quoteManager */
$quoteManager = Bootstrap::getObjectManager()->create(
    CartManagementInterface::class
);
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = Bootstrap::getObjectManager()->create(
    CartRepositoryInterface::class
);
/** @var CartItemRepositoryInterface $cartItemRepository */
$cartItemRepository = Bootstrap::getObjectManager()->create(
    CartItemRepositoryInterface::class
);
$customer = $customerRepository->get('email@companyquote.com');
$quoteId = $quoteManager->createEmptyCartForCustomer($customer->getId());
$quote = $quoteRepository->get($quoteId);
/** @var CartItemInterface $item */
$item = Bootstrap::getObjectManager()->create(CartItemInterface::class);
$item->setQuoteId($quoteId);
$item->setSku('simple');
$item->setQty(5);
$cartItemRepository->save($item);
