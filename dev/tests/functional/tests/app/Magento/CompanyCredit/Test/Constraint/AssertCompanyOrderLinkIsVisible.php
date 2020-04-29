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
 * Assert company order link is visible.
 */
class AssertCompanyOrderLinkIsVisible extends AbstractConstraint
{
    /**
     * Assert company order link is visible.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        $orderId
    ) {
        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        \PHPUnit\Framework\Assert::assertTrue(
            $companyEdit->getCreditHistoryGrid()->getOrderLink($orderId)->isVisible(),
            'Company order link is not visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company order link is visible.';
    }
}
