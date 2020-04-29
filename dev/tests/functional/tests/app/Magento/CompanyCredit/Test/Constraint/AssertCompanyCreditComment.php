<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that credit conversion comment message is correct.
 */
class AssertCompanyCreditComment extends AbstractConstraint
{
    /**
     * Credit conversion comment message.
     */
    private $expectedMessage = 'changed the credit currency from %s to %s at the conversion rate of %s/%s %s.';

    /**
     * Assert that credit conversion comment message is correct.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param array $companies
     * @param array $rates
     * @param string $currencyToCode
     * @param string|null $currencyFromCode [optional]
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        array $companies,
        array $rates,
        $currencyToCode,
        $currencyFromCode = null
    ) {
        for ($i = 0; $i < count($companies); $i++) {
            $currencyFromCode = ($currencyFromCode) ?: $companies[$i]->getCurrencyCode();
            $message = sprintf(
                $this->expectedMessage,
                $currencyFromCode,
                $currencyToCode,
                $currencyFromCode,
                $currencyToCode,
                $rates[$i]
            );
            $companyIndex->open();
            $companyIndex->getGrid()->searchAndOpen(['company_name' => $companies[$i]->getCompanyName()]);
            $companyEdit->getCompanyForm()->openSection('company_credit');
            $companyEdit->getCreditHistoryGrid()->resetFilter();
            \PHPUnit\Framework\Assert::assertTrue(
                strpos($companyEdit->getCreditHistoryGrid()->getFirstRowGridValue('Comment'), $message) !== false,
                'Credit conversion comment message is incorrect.'
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
        return 'Credit conversion comment message is correct.';
    }
}
