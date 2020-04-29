<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertSubtotalInShoppingCart.
 * Assert that subtotal total in the shopping cart is equals to expected total.
 */
class AssertSubtotalInShoppingCart extends AbstractAssertForm
{
    /**
     * Assert that subtotal total in the shopping cart is equals to expected total.
     *
     * @param CheckoutCart $checkoutCart
     * @param float|int $subtotal
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, $subtotal)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $subtotal,
            $checkoutCart->getTotalsBlock()->getSubtotal(),
            'Subtotal price in the shopping cart not equals to the expected subtotal price.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subtotal in the shopping cart equals to expected total.';
    }
}
