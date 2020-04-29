<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\ObjectManager;

/**
 * Assert product price is correct in the shopping cart.
 */
class AssertProductPriceInShoppingCart extends AbstractConstraint
{
    /**
     * Assert product price is correct in the shopping cart.
     *
     * @param CheckoutCart $checkoutCart
     * @param array $productsPresentInCatalog
     * @param float|int $discount
     * @return void
     */
    public function processAssert(
        CheckoutCart $checkoutCart,
        array $productsPresentInCatalog,
        $discount
    ) {
        $product = $productsPresentInCatalog[0];
        $addToCartStep = ObjectManager::getInstance()->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => [$product]]
        );
        $addToCartStep->run();

        $checkoutCart->open();
        $cartItem = $checkoutCart->getCartBlock()->getCartItem($product);
        \PHPUnit\Framework\Assert::assertEquals(
            $cartItem->getPrice(),
            number_format($product->getPrice() * (100 - $discount) / 100, 2),
            'Product price in the shopping cart doesn\'t equal to expected price value.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price is correct in the shopping cart.';
    }
}
