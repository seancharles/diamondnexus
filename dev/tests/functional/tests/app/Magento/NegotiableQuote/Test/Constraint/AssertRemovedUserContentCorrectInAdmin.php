<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Assert that name, company admin name and email are correct.
 */
class AssertRemovedUserContentCorrectInAdmin extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Process assert.
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param array $adminQuote
     * @param array $subUserQuote
     * @param Customer $companyAdmin
     * @param Customer $subUser
     * @param Company $company
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        array $adminQuote,
        array $subUserQuote,
        Customer $companyAdmin,
        Customer $subUser,
        Company $company
    ) {
        $quotesToVerify = [
            $adminQuote['quote-name'] => $companyAdmin,
            $subUserQuote['quote-name'] => $subUser
        ];

        foreach ($quotesToVerify as $name => $user) {
            $negotiableQuoteGrid->open();
            $filter = ['quote_name' => $name];
            $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
            $this->checkStatusIsCorrect($negotiableQuoteEdit);
            $this->checkUserIsCorrect($user, $negotiableQuoteEdit);
            $this->checkCompanyNameIsCorrect($company, $negotiableQuoteEdit);
            $this->checkCompanyEmailIsCorrect($companyAdmin, $negotiableQuoteEdit);
        }
    }

    /**
     * Check quote status is correct.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkStatusIsCorrect(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            'Closed',
            $negotiableQuoteEdit->getQuoteDetails()->getQuoteStatus(),
            'Quote status is not correct.'
        );
    }

    /**
     * Check created by user name is correct.
     *
     * @param Customer $user
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkUserIsCorrect(Customer $user, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $result = (bool)strpos($negotiableQuoteEdit->getQuoteDetails()->getCreatedBy(), $user->getLastname());

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Created by user name is not correct.'
        );
    }

    /**
     * Check company name is correct.
     *
     * @param Company $company
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkCompanyNameIsCorrect(Company $company, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $company->getCompanyName(),
            $negotiableQuoteEdit->getQuoteDetails()->getCompanyName(),
            'Quote company name is not correct.'
        );
    }

    /**
     * Check company email is correct.
     *
     * @param Customer $companyAdmin
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkCompanyEmailIsCorrect(Customer $companyAdmin, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $companyAdmin->getEmail(),
            $negotiableQuoteEdit->getQuoteDetails()->getCompanyAdminEmail(),
            'Quote company email is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote contents of removed users is correct.';
    }
}
