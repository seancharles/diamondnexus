<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert company is absent in grid.
 */
class AssertCompanyNotInGrid extends AbstractConstraint
{

    /**
     * Assert company is absent in grid.
     *
     * @param CompanyIndex $companyIndex
     * @param Company $company
     */
    public function processAssert(CompanyIndex $companyIndex, Company $company)
    {
        $filter = ['company_name' => $company->getCompanyName()];
        \PHPUnit\Framework\Assert::assertFalse(
            $companyIndex->getGrid()->isRowVisible($filter),
            'Company \'' . $company->getCompanyName() . '\' is present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company is not present in pages grid.';
    }
}
