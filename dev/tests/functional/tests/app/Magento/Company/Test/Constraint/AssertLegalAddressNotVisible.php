<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\CompanyProfile as CompanyProfilePage;

/**
 * Assert that legal address is not visible when it is restricted.
 */
class AssertLegalAddressNotVisible extends AbstractConstraint
{
    /**
     * @param CompanyProfilePage $companyProfilePage
     * @return void
     */
    public function processAssert(
        CompanyProfilePage $companyProfilePage
    ) {
        $companyProfilePage->open();

        \PHPUnit\Framework\Assert::assertFalse(
            $companyProfilePage->getProfileContent()->isLegalAddressSectionVisible(),
            'Legal address is visible when it should not.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Legal address is not visible.';
    }
}
