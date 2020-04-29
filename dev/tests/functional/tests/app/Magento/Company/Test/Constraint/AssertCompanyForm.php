<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that displayed company data on edit page equals passed from fixture.
 */
class AssertCompanyForm extends AbstractAssertForm
{

    /**
     * Assert that company data in form is equal to the original one.
     *
     * @param Company $company
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @return void
     * @throws \Exception
     */
    public function processAssert(
        Company $company,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $companyIndex->open();
        $filter = ['company_name' => $company->getCompanyName()];
        $companyIndex->getGrid()->searchAndOpen($filter);

        $companyFormData = $companyEdit->getCompanyForm()->getData($company);
        $errors = $this->verifyData($this->prepareCompanyData($company->getData()), $companyFormData);
        \PHPUnit\Framework\Assert::assertEmpty($errors, $errors);
    }

    /**
     * Replace customer_group_id by customer group option text.
     *
     * @param array $companyFromData
     * @return array
     */
    private function prepareCompanyData(array $companyFromData)
    {
        if (isset($companyFromData['customer_group_id']) && $companyFromData['customer_group_id'] == 'General') {
            $companyFromData['customer_group_id'] = 'Default (General)';
        }

        return $companyFromData;
    }

    /**
     * Company data on edit page equals data from fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company data on edit page equals data from fixture.';
    }
}
