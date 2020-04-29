<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart as CartPage;

/**
 * Check that cart contains right products.
 */
class AssertProductsInCart extends AbstractConstraint
{
    /**
     * Assert that names in cart match original ones
     *
     * @param CartPage $cartPage
     * @param array $products
     */
    public function processAssert(
        CartPage $cartPage,
        array $products
    ) {
        $cartPage->open();

        $nameArr = [];
        $cartNameArr = [];
        foreach ($products as $product) {
            $nameArr[] = $product->getData('name');
            $cartNameArr[] = $cartPage->getCartBlock()->getCartItem($product)->getName();
        }

        $result = array_diff($nameArr, $cartNameArr);

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Cart products are not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cart products are correct.';
    }
}
