<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;

/**
 * Check no quote in grid
 */
class AssertNoQuoteInGridInAdmin extends AbstractConstraint
{
    /**
     * Assert no quote in grid
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param array $quote
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        array $quote
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->search($filter);

        \PHPUnit\Framework\Assert::assertFalse(
            $negotiableQuoteGrid->getGrid()->isFirstRowVisible(),
            'Quote is not absent in grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote is absent in grid.';
    }
}
