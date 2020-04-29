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
 * Verify display currency notification message.
 */
class AssertQuoteDisplayCurrencyMessage extends AbstractConstraint
{
    /**
     * Expected notification message on negotiable quote.
     *
     * @var string
     */
    private $expectedMessage = 'This quote will be charged in %s. The prices are given in %s as reference only.';

    /**
     * Verify display currency notification message.
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $currencyInfo
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $currencyInfo
    ) {
        $message = sprintf(
            $this->expectedMessage,
            $currencyInfo['base_currency'],
            $currencyInfo['display_currency']
        );
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();
        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $negotiableQuoteView->getQuoteDetails()->getNotificationMessage(),
            'Notification message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Notification message is correct.';
    }
}
