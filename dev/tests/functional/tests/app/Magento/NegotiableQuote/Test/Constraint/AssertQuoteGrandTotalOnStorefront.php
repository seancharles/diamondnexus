<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Assert that Quote Grand Total is correct on quote page in the Storefront
 */
class AssertQuoteGrandTotalOnStorefront extends AbstractConstraint
{
    /**
     * Assert that Quote Grand Total is correct on quote page in the Storefront
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $prices
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $prices
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();
        $totals = $negotiableQuoteView->getQuoteDetails()->getTotals();

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
