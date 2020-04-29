<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;

/**
 * Assert that quote contains correct simple product
 */
class AssertCorrectSimpleProductInQuote extends AbstractConstraint
{
    /**
     * Assert that quote contains correct simple product
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $quote
     * @param CatalogProductSimple $simpleProduct
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $quote,
        CatalogProductSimple $simpleProduct
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $result = in_array($simpleProduct->getData('sku'), $negotiableQuoteView->getQuoteDetails()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Simple product is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Simple product is correct.';
    }
}
