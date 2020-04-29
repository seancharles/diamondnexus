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
 * Assert that quote status is set to 'Declined' after Mass Decline
 */
class AssertQuoteStatusIsDeclined extends AbstractConstraint
{
    /**
     * Assert that quote status is set to 'Declined'
     *
     * @param NegotiableQuoteIndex $quoteGrid
     * @param NegotiableQuoteEdit $quoteView
     * @param array $quote
     */
    public function processAssert(
        NegotiableQuoteIndex $quoteGrid,
        NegotiableQuoteEdit $quoteView,
        $quote
    ) {
        $quoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $quoteGrid->getGrid()->searchAndOpen($filter);

        \PHPUnit\Framework\Assert::assertEquals(
            'Declined',
            $quoteView->getQuoteDetails()->getQuoteStatus(),
            'Quote status is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote status is correct.';
    }
}
