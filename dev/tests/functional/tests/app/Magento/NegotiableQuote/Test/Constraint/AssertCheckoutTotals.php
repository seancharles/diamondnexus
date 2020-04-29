<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that checkout totals are correct on order page in the Storefront
 */
class AssertCheckoutTotals extends AbstractConstraint
{
    /**
     * Assert that checkout totals are correct
     *
     * @param array $actualPrices
     * @param array $checkoutPrices
     * @return void
     */
    public function processAssert(
        array $actualPrices,
        array $checkoutPrices
    ) {
        //Frontend order prices verification
        \PHPUnit\Framework\Assert::assertEquals(
            $checkoutPrices,
            array_filter($actualPrices),
            'Prices on order view page should be equal to defined in dataset.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString(): string
    {
        return 'Total prices equals to expected prices from data set.';
    }
}
