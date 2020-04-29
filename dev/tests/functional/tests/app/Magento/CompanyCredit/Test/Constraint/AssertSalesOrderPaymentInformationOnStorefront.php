<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that payment information is correct on the storefront.
 */
class AssertSalesOrderPaymentInformationOnStorefront extends AbstractConstraint
{
    /**
     * Assert that payment information is correct on the storefront.
     *
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $customerOrderView
     * @param string $orderId
     * @param string $paymentMethod
     * @param string $poNumber
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $customerOrderView,
        $orderId,
        $paymentMethod,
        $poNumber = ''
    ) {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        \PHPUnit\Framework\Assert::assertEquals(
            $paymentMethod,
            $customerOrderView->getPaymentInformation()->getPaymentMethod(),
            'Payment method is incorrect.'
        );
        if (!empty($poNumber)) {
            \PHPUnit\Framework\Assert::assertEquals(
                $poNumber,
                $customerOrderView->getPaymentInformation()->getPurchaseOrderNumber(),
                'Purchase order number is incorrect.'
            );
        }
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Payment information is correct on the storefront.";
    }
}
