<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct reject reason is specified
 */
class AssertCompanyRejectReason extends AbstractConstraint
{
    /**
     * Assert that correct reject reason is specified
     *
     * @param Company $company
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     */
    public function processAssert(
        Company $company,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $companyIndex->open();
        $filter = ['company_name' => $company->getCompanyName()];
        $companyIndex->getGrid()->searchAndOpen($filter);

        \PHPUnit\Framework\Assert::assertEquals(
            $company->getRejectReason(),
            $companyEdit->getCompanyForm()->getReasonForReject(),
            'Incorrect reject reason is specified.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Reject reason is correct.';
    }
}
