<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Assert that checkout links are not visible
 */
class AssertCheckoutLinksAreNotVisible extends AbstractConstraint
{

    /**
     * Assert that checkout links are not visible
     *
     * @param CheckoutCart $checkoutCart
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        $result = $checkoutCart->getCheckoutMethodsBlock()->isProceedToCheckoutVisible() &&
            $checkoutCart->getCheckoutMethodsBlock()->isMultipleAddressesCheckoutVisible();
        \PHPUnit\Framework\Assert::assertFalse(
            $result,
            'Checkout links are visible.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout links are not visible.';
    }
}
