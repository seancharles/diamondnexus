<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that order is correct in Admin.
 */
class AssertOrderCorrectInAdmin extends AbstractConstraint
{
    /**
     * Assert that order is correct in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param array $orderPrices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        array $orderPrices
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $this->checkTotals($salesOrderView, $orderPrices);
    }

    /**
     * Assert that order is correct in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param array $orderPrices
     * @return void
     */
    public function checkTotals(
        SalesOrderView $salesOrderView,
        array $orderPrices
    ) {
        $totals = $salesOrderView->getNegotiableSectionTotalsBlock()->getTotals();

        \PHPUnit\Framework\Assert::assertEquals(
            $orderPrices,
            array_filter($totals),
            'Prices on order view page should be equal to defined in dataset.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order totals are correct.';
    }
}
