<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Class AssertSidebar.
 *
 * Check the Sidebar blocks.
 */
class AssertSidebar extends AbstractConstraint
{
    /**
     * Check the Sidebar blocks.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param array $sidebarBlocks
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        array $sidebarBlocks
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            (bool) $sidebarBlocks['wishlist'],
            $customerAccountIndex->getWishlistBlock()->isVisible(),
            'Wishlist block visibility is not correct.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            (bool) $sidebarBlocks['compare'],
            $customerAccountIndex->getCompareBlock()->isVisible(),
            'Compare block visibility is not correct.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            (bool) $sidebarBlocks['reorder'],
            $customerAccountIndex->getReorderBlock()->isVisible(),
            'Reorder block visibility is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sidebar block visibility is correct.';
    }
}
