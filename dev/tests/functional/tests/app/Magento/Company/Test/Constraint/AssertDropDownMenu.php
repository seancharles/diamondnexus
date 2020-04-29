<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Class AssertDropDownMenu.
 *
 * Check the drop-down menu.
 */
class AssertDropDownMenu extends AbstractConstraint
{
    /**
     * Check the drop-down menu.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param array $headerMenuLinks
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex, array $headerMenuLinks)
    {
        $customerAccountIndex->getHeaderBlock()->clickMenu();
        \PHPUnit\Framework\Assert::assertEquals(
            $headerMenuLinks,
            $customerAccountIndex->getHeaderBlock()->getMenuItems(),
            'Drop-down menu is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Drop-down menu is correct.';
    }
}
