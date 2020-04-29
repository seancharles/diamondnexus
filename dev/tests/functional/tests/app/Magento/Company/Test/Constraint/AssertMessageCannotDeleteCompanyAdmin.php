<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Assert that correct error message is displayed
 */
class AssertMessageCannotDeleteCompanyAdmin extends AbstractConstraint
{
    /**
     * Assert that correct error message is displayed.
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @return void
     */
    public function processAssert(CustomerIndexEdit $customerIndexEdit)
    {
        $message = 'Sorry! You cannot delete this user: The user is the company admin.';
        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $customerIndexEdit->getCompanyModalBlock()->getAlertText(),
            'Error message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct error message is displayed.';
    }
}
