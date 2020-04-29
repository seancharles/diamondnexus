<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Class AssertQuoteShippingInfoInAdmin
 */
class AssertQuoteShippingInfoInAdmin extends AbstractConstraint
{
    /**
     * @var string
     */
    private $shippingMethodLabel = 'Shipping Methods & Price';

    /**
     * Process assert
     *
     * @param array $quote
     * @param NegotiableQuoteIndex $negotiableQuoteIndex
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $proposedShippingPrice
     * @throws \Exception
     */
    public function processAssert(
        array $quote,
        NegotiableQuoteIndex $negotiableQuoteIndex,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        $proposedShippingPrice
    ) {
        $negotiableQuoteIndex->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteIndex->getGrid()->searchAndOpen($filter);
        $this->assertShippingMethodLabel($negotiableQuoteEdit);
        $this->assertShippingProposedPriceValue($negotiableQuoteEdit, $proposedShippingPrice);
        $this->assertShippingAndHandlingValue($negotiableQuoteEdit, $proposedShippingPrice);
    }

    /**
     * toString
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipping method label is correct';
    }

    /**
     * Assert Shipping Method Label
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function assertShippingMethodLabel(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $shippingMethodLabel = $negotiableQuoteEdit->getQuoteShippingInformation()->getShippingMethodLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->shippingMethodLabel,
            $shippingMethodLabel,
            'Shipping method label is not correct'
        );
    }

    /**
     * Assert shipping proposed price value
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $expectedProposedShippingPrice
     */
    private function assertShippingProposedPriceValue(
        NegotiableQuoteEdit $negotiableQuoteEdit,
        $expectedProposedShippingPrice
    ) {
        $proposedShippingPrice = $negotiableQuoteEdit->getQuoteDetails()->getProposedShippingPrice();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedProposedShippingPrice,
            $proposedShippingPrice,
            'Proposed shipping price value is not correct'
        );
    }

    /**
     * Assert shipping and handling price value
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $expectedShippingAndHandlingPrice
     */
    private function assertShippingAndHandlingValue(
        NegotiableQuoteEdit $negotiableQuoteEdit,
        $expectedShippingAndHandlingPrice
    ) {
        $expectedShippingAndHandlingPrice = '$' .$expectedShippingAndHandlingPrice .'.00';
        $shippingAndHandlingPrice = $negotiableQuoteEdit->getQuoteDetails()->getShippingAndHandlingPrice();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedShippingAndHandlingPrice,
            $shippingAndHandlingPrice,
            'Shipping and handling price value is not correct'
        );
    }
}
