<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Assert that Quote Grand Total is correct on quote page in Admin.
 */
class AssertQuoteGrandTotalInAdmin extends AbstractConstraint
{
    /**
     * Assert that Quote Grand Total is correct on quote page in Admin.
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param array $prices
     * @param array $quote
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        array $prices,
        array $quote
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $totals = $negotiableQuoteEdit->getQuoteDetails()->getTotals();

        \PHPUnit\Framework\Assert::assertEquals(
            $prices['quoteGrandTotal'],
            $totals['grand_total'],
            'Grand Total price does not equal to price from data set.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand Total price equals to price from data set.';
    }
}
