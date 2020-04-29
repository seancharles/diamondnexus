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
 * Class checks that credit currency is correct.
 */
class AssertCreditCurrencyAfterCancelCurrencyConvertInAdmin extends AbstractConstraint
{
    /**
     * Assert reimburse balance amount currency symbol is correct.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param string $currencySymbol
     * @param string $creditCurrencyCode
     * @param string $creditCurrency
     * @param string $currencyRate
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        $currencySymbol,
        $creditCurrencyCode,
        $creditCurrency,
        $currencyRate
    ) {
        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        $companyEdit->getCompanyCreditForm()->selectCurrencyInDropdown($creditCurrency);
        $companyEdit->getCurrencyRatePopup()->setCurrencyRate($currencyRate);
        $companyEdit->getCurrencyRatePopup()->cancel();

        \PHPUnit\Framework\Assert::assertEquals(
            $creditCurrencyCode,
            $companyEdit->getCompanyCreditForm()->getCreditCurrencyValue(),
            'Credit currency is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $currencySymbol,
            $companyEdit->getCompanyCreditForm()->getCreditCurrencySymbol(),
            'Credit limit currency symbol is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit currency value and credit currency symbol are correct.';
    }
}
