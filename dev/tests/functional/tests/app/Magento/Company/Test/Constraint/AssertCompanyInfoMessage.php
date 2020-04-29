<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert Info message is displayed.
 */
class AssertCompanyInfoMessage extends AbstractConstraint
{
    /**
     * Assert Info message is displayed.
     *
     * @param CompanyPage $companyPage
     * @return void
     */
    public function processAssert(CompanyPage $companyPage)
    {
        $message = 'You don\'t have a company account yet.';
        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $companyPage->getManageCompany()->getInfoMessage(),
            'Info message is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Info message is displayed.";
    }
}
