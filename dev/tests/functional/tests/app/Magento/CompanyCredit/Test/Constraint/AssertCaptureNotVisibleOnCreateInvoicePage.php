<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Capture amount is not visible.
 */
class AssertCaptureNotVisibleOnCreateInvoicePage extends AbstractConstraint
{
    /**
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        OrderInvoiceNew $orderInvoiceNew,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $salesOrderView->getPageActions()->invoice();
        \PHPUnit\Framework\Assert::assertFalse(
            $orderInvoiceNew->getTotalsBlock()->captureIsVisible(),
            'Capture amount is visible.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Capture amount is not visible";
    }
}
