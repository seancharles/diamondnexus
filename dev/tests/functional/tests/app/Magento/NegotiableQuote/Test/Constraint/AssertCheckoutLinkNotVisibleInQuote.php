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
 * Check that checkout button is not visible in quote
 */
class AssertCheckoutLinkNotVisibleInQuote extends AbstractConstraint
{
    /**
     * Check that checkout button is not visible in quote
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();

        \PHPUnit\Framework\Assert::assertFalse(
            $negotiableQuoteView->getQuoteDetails()->isCheckoutButtonVisible(),
            'Checkout button is visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout button is not visible.';
    }
}
