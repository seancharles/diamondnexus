<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Check that quote re-submit message is displayed
 *
 */
class AssertQuoteReSubmitMessage extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function processAssert(
        NegotiableQuoteView $negotiableQuoteView
    ) {
        $message = 'You have added or changed the shipping address on this quote.' .
            ' Please re-submit the quote to Seller.';

        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $negotiableQuoteView->getMessagesBlock()->getNoticeMessage(),
            'Quote re-submit message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote re-submit message is correct.';
    }
}
