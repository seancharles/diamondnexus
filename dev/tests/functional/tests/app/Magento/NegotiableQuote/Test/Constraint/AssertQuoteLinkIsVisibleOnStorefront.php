<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Assert that quote link is correct on order page in Storefront
 */
class AssertQuoteLinkIsVisibleOnStorefront extends AbstractConstraint
{
    /**
     * Assert that quote link is correct on order page on Storefront
     *
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param NegotiableQuoteView $quoteView
     * @param int $orderId
     * @param array $quote
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        NegotiableQuoteView $quoteView,
        $orderId,
        array $quote
    ) {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        $this->checkQuoteLinkIsCorrect($orderView, $quote);
        $this->verifyNegotiableQuotePage($orderView, $quoteView, $quote);
    }

    /**
     * Verify whether quote link is correct
     *
     * @param CustomerOrderView $orderView
     * @param array $quote
     */
    public function checkQuoteLinkIsCorrect(CustomerOrderView $orderView, $quote)
    {
        $quoteMessage = 'Order Placed From Quote: ' . $quote['quote-name'];
        \PHPUnit\Framework\Assert::assertEquals(
            $quoteMessage,
            $orderView->getNegotiableQuoteInformation()->getOrderQuoteText(),
            'Quote link is not correct.'
        );
    }

    /**
     * Verify whether correct page is displayed
     *
     * @param CustomerOrderView $orderView
     * @param NegotiableQuoteView $quoteView
     * @param array $quote
     */
    public function verifyNegotiableQuotePage(CustomerOrderView $orderView, NegotiableQuoteView $quoteView, $quote)
    {
        $orderView->getNegotiableQuoteInformation()->clickQuoteLink();
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $quoteView->getQuoteDetails()->getQuoteName(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote link is correct on Storefront.';
    }
}
