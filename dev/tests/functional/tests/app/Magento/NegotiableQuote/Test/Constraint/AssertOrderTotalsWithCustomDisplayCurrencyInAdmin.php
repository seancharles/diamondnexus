<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;

/**
 * Assert that prices in base and display currencies are displayed in order when they differ.
 */
class AssertOrderTotalsWithCustomDisplayCurrencyInAdmin extends AbstractConstraint
{
    /**
     * Assert that prices in base and display currencies are displayed in order when they differ.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param array $expectedTotals
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        array $expectedTotals,
        $orderId
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $totals = $salesOrderView->getNegotiableSectionTotalsBlock()->getTotalsWithDifferentCurrencies();
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedTotals['catalog_total_price'],
            $totals['col-catalog_price'],
            'Catalog total prices are incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedTotals['negotiated_discount'],
            $totals['col-negotiated_discount'],
            'Negotiated discount is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedTotals['subtotal'],
            $totals['col-subtotal'],
            'Subtotal is incorrect.'
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
