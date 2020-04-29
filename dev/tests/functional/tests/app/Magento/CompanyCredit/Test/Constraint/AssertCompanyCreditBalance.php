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
 * Assert company credit balance is valid.
 */
class AssertCompanyCreditBalance extends AbstractConstraint
{
    /**
     * Assert company company credit balance is valid.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param array $amounts
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        array $amounts
    ) {
        $keysToRemove = ['reimburse', 'refund'];
        $amounts = array_diff_key($amounts, array_flip($keysToRemove));

        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        foreach ($amounts as $key => $expectedValue) {
            \PHPUnit\Framework\Assert::assertSame(
                (float)$expectedValue,
                $companyEdit->getCreditBalanceInformation()->getCreditBalanceValue($key),
                'Company credit balance is incorrect.'
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
        return 'Company credit balance is valid.';
    }
}
