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
 * Assert company credit balance history is valid.
 */
class AssertCompanyCreditBalanceHistoryOperations extends AbstractConstraint
{
    /**
     * Assert company credit balance history is valid.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param array $operations
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        array $operations
    ) {
        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        foreach ($operations as $operation) {
            \PHPUnit\Framework\Assert::assertTrue(
                $companyEdit->getCreditHistoryGrid()->getCreditBalanceHistoryRow($operation)->isVisible(),
                sprintf('Operation \'%s\' is missing in credit balance history.', $operation)
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
        return 'Company credit balance history is valid.';
    }
}
