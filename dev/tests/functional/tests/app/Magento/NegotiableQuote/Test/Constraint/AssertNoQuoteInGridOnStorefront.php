<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;

/**
 * Check no quote in grid
 */
class AssertNoQuoteInGridOnStorefront extends AbstractConstraint
{
    /**
     * Assert no quote in grid
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid
    ) {
        $negotiableQuoteGrid->open();

        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteGrid->getQuoteGrid()->isEmpty(),
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
