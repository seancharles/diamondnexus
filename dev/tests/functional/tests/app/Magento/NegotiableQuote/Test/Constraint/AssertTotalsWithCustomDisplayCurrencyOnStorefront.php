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
 * Assert that prices in base and display currencies are displayed in quote when they differ.
 */
class AssertTotalsWithCustomDisplayCurrencyOnStorefront extends AbstractConstraint
{
    /**
     * Assert that prices in base and display currencies are displayed in quote when they differ.
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $expectedTotals
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $expectedTotals
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();
        $totals = $negotiableQuoteView->getQuoteDetails()->getTotals();
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedTotals['total'],
            $totals['grand_total'],
            'Price in display currency is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedTotals['to_be_charged'],
            $totals['base_grand_total'],
            'Price in base currency is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '"Grand total" in display currency and "To be charged" value in base currency are correct.';
    }
}
