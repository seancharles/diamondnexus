<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert company credit balance and set of history operations.
 */
class AssertCompanyCreditState extends AbstractConstraint
{
    /**
     * Assert company credit balance and set of history operations.
     *
     * @param CompanyEdit $companyEdit
     * @param array $companies
     * @param array $expectedCreditState
     * @return void
     */
    public function processAssert(
        CompanyEdit $companyEdit,
        array $companies,
        array $expectedCreditState
    ) {
        foreach ($companies as $key => $company) {
            $companyEdit->open(['id' => $company->getId()]);
            $companyEdit->getCompanyForm()->openSection('company_credit');
            if (!empty($expectedCreditState['amounts'])) {
                foreach ($expectedCreditState['amounts'] as $key => $expectedValue) {
                    \PHPUnit\Framework\Assert::assertSame(
                        (float)$expectedValue,
                        $companyEdit->getCompanyCreditForm()->getCreditBalanceValue($key),
                        sprintf('Company credit balance (%s) is incorrect.', $key)
                    );
                }
            }
            if (!empty($expectedCreditState['operations'])) {
                foreach ($expectedCreditState['operations'] as $operation) {
                    \PHPUnit\Framework\Assert::assertTrue(
                        $companyEdit->getCompanyCreditForm()->getCreditBalanceHistoryRow($operation)->isVisible(),
                        sprintf('Operation \'%s\' is missing in credit balance history.', $operation)
                    );
                }
            }
            if (!empty($expectedCreditState['missingOperations'])) {
                foreach ($expectedCreditState['missingOperations'] as $operation) {
                    \PHPUnit\Framework\Assert::assertFalse(
                        $companyEdit->getCompanyCreditForm()->getCreditBalanceHistoryRow($operation)->isVisible(),
                        sprintf('Operation \'%s\' is missing in credit balance history.', $operation)
                    );
                }
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company credit balance and set of history operations are correct.';
    }
}
