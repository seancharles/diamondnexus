<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Assert that quote shipping address is correct
 */
class AssertQuoteAddressIsCorrect extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $shippingAddress
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $shippingAddress
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();

        $selectedAddress = explode(
            "\n",
            $negotiableQuoteView->getQuoteDetails()->getShippingAddress()
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
        $pattern[] = $address['company'];
        $pattern[] = $address['street'];
        $pattern[] = $address['city'] . ", " . $region . ", " . $address['postcode'];
        $pattern[] = $address['country_id'];
        $pattern[] = 'T: ' . $address['telephone'];

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
        return 'Quote shipping address is correct.';
    }
}
