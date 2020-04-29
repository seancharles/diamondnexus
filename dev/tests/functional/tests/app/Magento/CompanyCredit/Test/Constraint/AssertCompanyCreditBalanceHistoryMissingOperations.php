<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert operation is missing in the credit history grid.
 */
class AssertCompanyCreditBalanceHistoryMissingOperations extends AbstractConstraint
{
    /**
     * Assert operation is missing in the credit history grid.
     *
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param array $missingOperations
     * @return void
     */
    public function processAssert(
        CompanyEdit $companyEdit,
        Company $company,
        array $missingOperations
    ) {
        $companyEdit->open(['id' => $company->getId()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        foreach ($missingOperations as $operation) {
            \PHPUnit\Framework\Assert::assertFalse(
                $companyEdit->getCreditHistoryGrid()->isCreditBalanceHistoryRowVisible($operation),
                sprintf('Operation \'%s\' is present in credit balance history.', $operation)
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
        return 'Operation is missing in credit history grid.';
    }
}
