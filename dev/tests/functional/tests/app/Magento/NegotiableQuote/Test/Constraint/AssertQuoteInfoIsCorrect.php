<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutOnepage;

/**
 * Check quote information
 */
class AssertQuoteInfoIsCorrect extends AbstractConstraint
{
    /**
     * Assert that quote info is correct
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $shippingAddress
     * @param array $shippingMethod
     * @param string $paymentMethod
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage,
        array $shippingAddress,
        array $shippingMethod,
        $paymentMethod
    ) {
        $this->verifyAddress($shippingAddress, $checkoutOnepage);
        $this->verifyShippingMethod($shippingMethod, $checkoutOnepage);
        $this->verifyPaymentMethod($paymentMethod, $checkoutOnepage);
    }

    /**
     * Verify shipping address
     *
     * @param array $shippingAddress
     * @param CheckoutOnepage $checkoutOnepage
     */
    protected function verifyAddress(array $shippingAddress, CheckoutOnepage $checkoutOnepage)
    {
        $selectedAddress = explode(
            "\n",
            $checkoutOnepage->getQuoteShippingBlock()->getShippingAddress()
        );
        $pattern = $this->makeAddressPattern($shippingAddress);
        $dataDiff = $this->verifyAddressMatch($pattern, $selectedAddress);
        \PHPUnit\Framework\Assert::assertEmpty(
            $dataDiff,
            'Shipping addresses don\'t match.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * Verify shipping method
     *
     * @param array $shippingMethod
     * @param CheckoutOnepage $checkoutOnepage
     */
    protected function verifyShippingMethod(array $shippingMethod, CheckoutOnepage $checkoutOnepage)
    {
        $result = true;
        $pattern = $this->makeShippingMethodPattern($shippingMethod);
        if ($pattern != $checkoutOnepage->getQuoteShippingMethodBlock()->getShippingMethod()) {
            $result = false;
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Shipping methods don\'t match.'
        );
    }

    /**
     * Verify payment method
     *
     * @param string $paymentMethod
     * @param CheckoutOnepage $checkoutOnepage
     */
    protected function verifyPaymentMethod($paymentMethod, CheckoutOnepage $checkoutOnepage)
    {
        $result = true;
        if ($paymentMethod != $checkoutOnepage->getQuotePaymentBlock()->getPaymentMethod()) {
            $result = false;
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Payment methods don\'t match.'
        );
    }

    /**
     * Make pattern for address verifying
     *
     * @param array $address
     * @return array
     */
    protected function makeAddressPattern(array $address)
    {
        $pattern = [];
        $regionId = $address['region_id'];
        $region = $regionId ? $regionId : $address['region'];

        $pattern[] = $address['firstname'] . " " . $address['lastname'];
        $pattern[] = $address['street'];
        $pattern[] = $address['city'] . ", " . $region . " " . $address['postcode'];
        $pattern[] = $address['country_id'];
        $pattern[] = $address['telephone'];

        return $pattern;
    }

    /**
     * Make pattern for shipping method verifying
     *
     * @param array $shippingMethod
     * @return string
     */
    protected function makeShippingMethodPattern(array $shippingMethod)
    {
        $pattern = $shippingMethod['shipping_service'] . ' - ' . $shippingMethod['shipping_method'];

        return $pattern;
    }

    /**
     * Verify that shipping addresses match
     *
     * @param array $pattern
     * @param array $address
     * @return array
     */
    protected function verifyAddressMatch(array $pattern, array $address)
    {
        $errorMessages = [];
        foreach ($pattern as $value) {
            if (!in_array($value, $address)) {
                $errorMessages[] = "Data '$value' in fields is not found.";
            }
        }
        return $errorMessages;
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
