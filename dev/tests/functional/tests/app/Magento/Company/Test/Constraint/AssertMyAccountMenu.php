<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Class AssertMyAccountMenu.
 *
 * Check "My Account" menu.
 */
class AssertMyAccountMenu extends AbstractConstraint
{
    /**
     * Check "My Account" menu.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param array $myAccountMenuLinks
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex, array $myAccountMenuLinks)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $myAccountMenuLinks,
            $customerAccountIndex->getAccountMenu()->getMenuItems(),
            'Account menu is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Account menu is correct.';
    }
}
