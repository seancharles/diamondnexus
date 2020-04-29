<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;

/**
 * Assert that quote contains correct grouped product
 */
class AssertCorrectGroupedProductInQuote extends AbstractConstraint
{
    /**
     * Assert that quote contains correct grouped product
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $quote
     * @param GroupedProduct $groupedProduct
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $quote,
        GroupedProduct $groupedProduct = null
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $skuArr = [];
        $simpleProducts = $groupedProduct->getData('associated')['products'];
        foreach ($simpleProducts as $product) {
            $skuArr[] = $product->getSku();
        }

        $result = array_diff($skuArr, $negotiableQuoteView->getQuoteDetails()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Grouped product is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Grouped product is correct.';
    }
}
