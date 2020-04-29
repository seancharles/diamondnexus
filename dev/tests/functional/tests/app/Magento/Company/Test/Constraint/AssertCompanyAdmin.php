<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert company admin is correct.
 */
class AssertCompanyAdmin extends AbstractConstraint
{
    /**
     * Assert company admin is correct.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        Customer $customer
    ) {
        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        \PHPUnit\Framework\Assert::assertEquals(
            $customer->getEmail(),
            $companyEdit->getCompanyForm()->getAdminEmail(),
            'Company admin is different from \'' . $customer->getEmail() . '\''
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company admin is not correct.';
    }
}
