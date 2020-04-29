<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;

/**
 * Check that quick order form first item is correct
 */
class AssertQuickOrderItemsAfterRemove extends AbstractConstraint
{
    /**
     * Assert that quick order form first item is correct
     *
     * @param QuickOrderPage $quickOrderPage
     * @param array $products
     */
    public function processAssert(
        QuickOrderPage $quickOrderPage,
        array $products
    ) {
        array_shift($products);

        $sku = '';
        if (count($products)) {
            $firstProduct = array_shift($products);
            $sku = $firstProduct->getData('sku');
        }

        \PHPUnit\Framework\Assert::assertEquals(
            $quickOrderPage->getItems()->getFirstSku(),
            $sku,
            'Quick order form first item is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quick order form first item is correct.';
    }
}
