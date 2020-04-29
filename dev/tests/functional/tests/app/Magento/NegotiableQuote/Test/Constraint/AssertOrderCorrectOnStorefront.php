<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;

/**
 * Check that order contains correct data.
 */
class AssertOrderCorrectOnStorefront extends AbstractConstraint
{
    /**
     * Check that order contains correct data.
     *
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param int $orderId
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param \Magento\SalesRule\Test\Fixture\SalesRule $salesRule
     * @param string $discountType [optional]
     * @param int $discountValue [optional]
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        $orderId,
        array $products,
        array $qtys,
        $tax,
        $salesRule,
        $discountType = '',
        $discountValue = null
    ) {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);

        $this->checkOrderGrandTotal(
            $orderView,
            $products,
            $qtys,
            $tax,
            $salesRule->getData(),
            $discountType,
            $discountValue
        );
        $this->checkDiscountNotApplied($orderView);
    }

    /**
     * Check order grand total.
     *
     * @param CustomerOrderView $orderView
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param array $salesRule
     * @param string $discountType
     * @param int $discountValue
     * @return void
     */
    public function checkOrderGrandTotal(
        $orderView,
        $products,
        $qtys,
        $tax,
        $salesRule,
        $discountType,
        $discountValue
    ) {
        $totals = $orderView->getOrderTotalsBlock()->getTotals();
        $shippingAmount = (int)substr($totals['shipping'], 1);
        $result = true;
        $subtotal = 0;
        $i = 0;

        foreach ($products as $product) {
            $price = $product->getData('price') * $qtys[$i] * (100 - $salesRule['discount_amount']) / 100;
            $subtotal += $price;
            $i++;
        }

        switch ($discountType) {
            case 'amount':
                $subtotal = $subtotal - $discountValue;
                break;
            case 'percentage':
                $subtotal = $subtotal - ($subtotal * $discountValue / 100);
                break;
            case 'proposed':
                $subtotal = $discountValue;
                break;
        }

        $grandTotal = number_format(($subtotal + $shippingAmount + ($subtotal * $tax / 100)), 2);
        if (strpos($totals['grand_total_incl'], $grandTotal) === false) {
            $result = false;
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Order grand total is not correct.'
        );
    }

    /**
     * Check that discount is not applied.
     *
     * @param CustomerOrderView $orderView
     * @return void
     */
    protected function checkDiscountNotApplied(CustomerOrderView $orderView)
    {
        $result = true;
        $totals = $orderView->getOrderTotalsBlock()->getTotals();
        if (isset($totals['discount'])) {
            $result = false;
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Discount is present in order totals.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order data is correct.';
    }
}
