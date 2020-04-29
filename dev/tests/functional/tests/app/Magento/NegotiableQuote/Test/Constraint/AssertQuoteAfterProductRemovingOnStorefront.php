<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Assert that deleted product is not displayed in negotiable quote.
 */
class AssertQuoteAfterProductRemovingOnStorefront extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Assert that deleted product is not displayed in negotiable quote.
     *
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array               $deletedProducts
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $deletedProducts
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();

        $deletedProductSkus = [];
        foreach ($deletedProducts as $deletedProduct) {
            $deletedProductSkus[] = $deletedProduct->getData('sku');
        }
        // List of skus of products in quote
        $quoteProductSkus = $negotiableQuoteView->getQuoteDetails()->getSkuList();

        $result = array_intersect($quoteProductSkus, $deletedProductSkus);
        // check are there deleted products in quote products list on quote page
        \PHPUnit\Framework\Assert::assertTrue(
            0 === count($result),
            'Deleted products are present in quote.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote is displayed correctly after product removing.';
    }
}
