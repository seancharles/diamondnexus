<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that payment information is correct.
 */
class AssertSalesOrderPaymentInformation extends AbstractConstraint
{
    /**
     * Assert that payment information is correct.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param string $paymentMethod
     * @param string $poNumber
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        $paymentMethod,
        $poNumber = ''
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        \PHPUnit\Framework\Assert::assertEquals(
            $paymentMethod,
            $salesOrderView->getPaymentInformation()->getPaymentMethod(),
            'Payment method is incorrect.'
        );
        if (!empty($poNumber)) {
            \PHPUnit\Framework\Assert::assertEquals(
                $poNumber,
                $salesOrderView->getPaymentInformation()->getPurchaseOrderNumber(),
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
        return "Payment information is correct.";
    }
}
