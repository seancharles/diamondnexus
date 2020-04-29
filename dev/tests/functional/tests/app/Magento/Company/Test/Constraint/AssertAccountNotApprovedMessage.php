<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Assert that correct error message is displayed
 */
class AssertAccountNotApprovedMessage extends AbstractConstraint
{

    /**
     * Assert that correct error message is displayed
     *
     * @param CustomerAccountLogin $customerAccountLogin
     */
    public function processAssert(CustomerAccountLogin $customerAccountLogin)
    {
        $message = 'Your account is not yet approved. If you have questions, please contact the seller.';
        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $customerAccountLogin->getMessages()->getErrorMessage(),
            'Error message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct error message is displayed.';
    }
}
