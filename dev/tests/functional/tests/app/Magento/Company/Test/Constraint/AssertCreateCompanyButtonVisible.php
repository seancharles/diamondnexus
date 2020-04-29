<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert that "Create a Company Account" button is displayed or hidden according to the settings.
 */
class AssertCreateCompanyButtonVisible extends AbstractConstraint
{
    /**
     * Assert that "Create a Company Account" button is displayed or hidden according to the settings.
     *
     * @param CompanyPage $companyPage
     * @param int $isButtonVisible
     * @return void
     */
    public function processAssert(CompanyPage $companyPage, $isButtonVisible)
    {
        if ($isButtonVisible) {
            \PHPUnit\Framework\Assert::assertTrue(
                $companyPage->getManageCompany()->isButtonVisible(),
                'Button is not visible.'
            );
        } else {
            \PHPUnit\Framework\Assert::assertFalse(
                $companyPage->getManageCompany()->isButtonVisible(),
                'Button is visible.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Test execution passed successfully.";
    }
}
