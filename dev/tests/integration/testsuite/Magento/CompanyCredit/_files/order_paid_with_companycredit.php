<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require __DIR__ . '/../../../Magento/Sales/_files/order_with_customer.php';
/** @var \Magento\Sales\Model\Order $order */
require 'company_with_credit_limit.php';
/** @var \Magento\Company\Model\Company $company */

$order->getPayment()
    ->setMethod(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME)
    ->setAdditionalInformation('company_id' , $company->getId())
    ->save();

$order->setSubtotal(20)
    ->setGrandTotal(20)
    ->setBaseSubtotal(20)
    ->setBaseGrandTotal(20)
    ->setCustomerId(1)
    ->setCustomerIsGuest(false)
    ->setCustomerEmail(null)
    ->save();

$orderService = \Magento\TestFramework\ObjectManager::getInstance()->create(
    \Magento\Sales\Api\InvoiceManagementInterface::class
);
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
$order = $invoice->getOrder();
$order->setIsInProcess(false);
$transactionSave = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\DB\Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();
