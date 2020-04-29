<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyPayment\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct payment methods are available while placing an order.
 */
class AssertCorrectPaymentMethods extends AbstractConstraint
{
    /**
     * Assert that correct payment methods are available while placing an order.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $expectedPaymentMethods
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage,
        $expectedPaymentMethods
    ) {
        $expectedMethodsArray = explode(',', $expectedPaymentMethods);
        $actualMethodsArray = $checkoutOnepage->getPaymentBlock()->getPaymentMethods();
        \PHPUnit\Framework\Assert::assertEquals(
            sort($expectedMethodsArray),
            sort($actualMethodsArray),
            'Payment methods available are not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Available payment methods match the expected ones.';
    }
}
