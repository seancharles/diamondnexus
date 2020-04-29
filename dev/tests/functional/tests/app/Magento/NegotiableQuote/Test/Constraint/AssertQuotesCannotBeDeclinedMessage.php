<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;

/**
 * Assert that correct message is displayed in quote decline popup.
 */
class AssertQuotesCannotBeDeclinedMessage extends AbstractConstraint
{
    /**
     * Assert that correct message is displayed in quote decline popup.
     *
     * @param NegotiableQuoteIndex $quoteGrid
     * @param string $declinePopupMessage
     * @return void
     */
    public function processAssert(
        NegotiableQuoteIndex $quoteGrid,
        $declinePopupMessage
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            $declinePopupMessage,
            $quoteGrid->getDeclinePopupBlock()->getNotificationMessage(),
            'Decline message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct message is displayed in quote decline popup.';
    }
}
