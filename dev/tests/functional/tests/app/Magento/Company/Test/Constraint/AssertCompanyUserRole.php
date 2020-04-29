<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert that company user role is correct.
 */
class AssertCompanyUserRole extends AbstractConstraint
{
    /**
     * Process assert.
     *
     * @param CompanyPage $companyPage
     * @param string $companyUserRole
     * @return void
     */
    public function processAssert(CompanyPage $companyPage, $companyUserRole)
    {
        $companyPage->open();
        $companyPage->getTree()->selectFirstChild();
        $companyPage->getTreeControl()->clickEditSelected();
        $userRole = $companyPage->getCustomerPopup()->getUserRole();

        \PHPUnit\Framework\Assert::assertEquals(
            $companyUserRole,
            $userRole,
            'Company user role is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company user role is correct.';
    }
}
