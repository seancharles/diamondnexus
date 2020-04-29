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
 * Class AssertQuoteShippingInfoOnStorefront
 */
class AssertQuoteShippingInfoOnStorefront extends AbstractConstraint
{
    /**
     * Process assert
     *
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendEdit
     * @param string $proposedShippingPrice
     */
    public function processAssert(
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendEdit,
        $proposedShippingPrice
    ) {
        $quoteFrontendGrid->open();
        $quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->assertProposedShippingPrice($quoteFrontendEdit, $proposedShippingPrice);
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
     * Assert proposed shipping price
     *
     * @param NegotiableQuoteView $quoteFrontendEdit
     * @param string $expectedProposedShippingPrice
     */
    private function assertProposedShippingPrice(NegotiableQuoteView $quoteFrontendEdit, $expectedProposedShippingPrice)
    {
        $proposedShippingPrice = $quoteFrontendEdit->getQuoteDetails()->getProposedShippingPrice();
        $expectedProposedShippingPrice = '$' .$expectedProposedShippingPrice .'.00';

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedProposedShippingPrice,
            $proposedShippingPrice,
            'Proposed shipping price value is not correct'
        );
    }
}
