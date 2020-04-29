<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Assert that block with items to be added to quote is absent on the page
 */
class AssertItemsBlockIsNotVisible extends AbstractConstraint
{
    /**
     * Assert that block with items to be added to quote is absent on the page
     *
     * @param NegotiableQuoteEdit $negotiableQuoteView
     */
    public function processAssert(
        NegotiableQuoteEdit $negotiableQuoteView
    ) {
        \PHPUnit\Framework\Assert::assertFalse(
            $negotiableQuoteView->getQuoteDetails()->isItemsBlockVisible(),
            'Block with items is visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Block with items is absent on the page.';
    }
}
