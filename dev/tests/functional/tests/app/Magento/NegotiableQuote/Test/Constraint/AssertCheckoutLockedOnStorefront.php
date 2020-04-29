<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\Checkout\Test\Page\CheckoutOnepage;

/**
 * Check checkout is locked
 */
class AssertCheckoutLockedOnStorefront extends AbstractConstraint
{
    /**
     * Assert checkout is locked
     *
     * @param NegotiableQuoteGrid $quoteGrid
     */
    public function processAssert(
        NegotiableQuoteGrid $quoteGrid,
        NegotiableQuoteView $quoteView,
        CheckoutOnepage $checkoutPage
    ) {
        $quoteGrid->open();
        $quoteGrid->getQuoteGrid()->openFirstItem();
        $quoteView->getQuoteDetails()->checkout();
        $this->checkNotificationMessage($checkoutPage);
    }

    /**
     * Check notification message
     *
     * @param CheckoutOnepage $checkoutPage
     */
    protected function checkNotificationMessage(CheckoutOnepage $checkoutPage)
    {
        $notificationMessage = 'The shipping address specified on the quote was deleted from your Address Book.' .
            ' To proceed with the checkout, go back to the quote and update the shipping address.';

        \PHPUnit\Framework\Assert::assertEquals(
            $notificationMessage,
            $checkoutPage->getShippingAddressBlock()->getMessage(),
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
        return 'Quote info is correct.';
    }
}
