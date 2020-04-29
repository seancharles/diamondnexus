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
 * Assert currency symbol near Amount input of Reimburse Balance popup.
 */
class AssertReimburseBalanceCurrencySymbol extends AbstractConstraint
{
    /**
     * Assert reimburse balance amount currency symbol is correct.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param string $currencySymbol
     * @param bool $openCompanyPage [optional]
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        $currencySymbol,
        $openCompanyPage = false
    ) {
        if ($openCompanyPage) {
            $companyIndex->open();
            $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        }

        $companyEdit->getCompanyCreditFormActions()->reimburseBalance();
        \PHPUnit\Framework\Assert::assertEquals(
            $currencySymbol,
            $companyEdit->getModalReimburseBalance()->getCurrencySymbol(),
            'Reimburse balance amount currency symbol is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Reimburse balance amount currency symbol is correct.';
    }
}
