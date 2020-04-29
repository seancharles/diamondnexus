<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Assert that quote contains correct bundle product.
 */
class AssertCorrectBundleProductInQuote extends AbstractConstraint
{
    /**
     * Assert that quote contains correct bundle product.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param BundleProduct $bundleProduct [optional]
     * @return void
     */
    public function processAssert(
        NegotiableQuoteEdit $negotiableQuoteView,
        BundleProduct $bundleProduct = null
    ) {
        $skuArr = [];
        $simpleProducts = $bundleProduct->getData('bundle_selections')['products'][0];
        $simpleProductSku = '';
        foreach ($simpleProducts as $product) {
            $simpleProductSku = $product->getSku();
            break;
        }
        $skuArr[] = $bundleProduct->getSku() . '-' . $simpleProductSku;
        $result = array_diff($skuArr, $negotiableQuoteView->getQuoteDetails()->getComplexProductsSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Bundle product is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle product is correct.';
    }
}
