<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Assert that quote link is correct on order page  in Admin.
 */
class AssertQuoteLinkIsVisibleInAdmin extends AbstractConstraint
{
    /**
     * Assert that quote link is correct on order page in Admin.
     *
     * @param OrderIndex $orderHistory
     * @param SalesOrderView $orderView
     * @param NegotiableQuoteEdit $quoteView
     * @param int $orderId
     * @param array $quote
     */
    public function processAssert(
        OrderIndex $orderHistory,
        SalesOrderView $orderView,
        NegotiableQuoteEdit $quoteView,
        $orderId,
        array $quote
    ) {
        $orderHistory->open();
        $orderHistory->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        $this->checkQuoteLinkIsCorrect($orderView, $quote);
        $this->verifyNegotiableQuotePage($orderView, $quoteView, $quote);
    }

    /**
     * Verify whether quote link is correct
     *
     * @param SalesOrderView $orderView
     * @param array $quote
     */
    public function checkQuoteLinkIsCorrect(SalesOrderView $orderView, $quote)
    {
        \PHPUnit\Framework\Assert::assertContains(
            $quote['quote-name'],
            $orderView->getNegotiableQuoteInformation()->getOrderQuoteText(),
            'Quote link is not correct.'
        );
    }

    /**
     * Verify whether correct page is displayed
     *
     * @param SalesOrderView $orderView
     * @param NegotiableQuoteEdit $quoteView
     * @param array $quote
     */
    public function verifyNegotiableQuotePage(SalesOrderView $orderView, NegotiableQuoteEdit $quoteView, $quote)
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
        return 'Quote link is correct in Admin Panel.';
    }
}
