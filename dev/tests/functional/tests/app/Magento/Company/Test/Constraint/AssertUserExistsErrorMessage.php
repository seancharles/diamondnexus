<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct message is displayed if the user is already assigned to a company
 */
class AssertUserExistsErrorMessage extends AbstractConstraint
{
    /**
     * Assert that correct message is displayed if the user is already assigned to a company
     *
     * @param CompanyPage $companyPage
     */
    public function processAssert(
        CompanyPage $companyPage
    ) {
        $errorMessage = 'A user with this email address already exists in the system. ' .
            'Enter a different email address to create this user.';

        \PHPUnit\Framework\Assert::assertEquals(
            $errorMessage,
            $companyPage->getCustomerPopup()->getErrorMessage(),
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
        return 'Error message is correct.';
    }
}
