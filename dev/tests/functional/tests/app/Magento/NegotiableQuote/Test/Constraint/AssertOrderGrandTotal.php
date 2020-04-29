<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Grand Total is correct on order page in the Storefront
 */
class AssertOrderGrandTotal extends AbstractConstraint
{
    /**
     * Assert that Order Grand Total is correct on order page in the Storefront
     *
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param string $orderId
     * @param array $prices
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        $orderId,
        array $prices
    ) {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        $totals = $orderView->getOrderTotalsBlock()->getTotals();

        \PHPUnit\Framework\Assert::assertEquals(
            $prices['grandTotal'],
            $totals['grand_total_incl'],
            'Grand Total price does not equal to price from data set.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand Total price equals to price from data set.';
    }
}
