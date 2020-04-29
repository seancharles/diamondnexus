<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Company\Test\Fixture\Company;

/**
 * Test Creation for DeleteCompanyEntity
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Stores>Companies
 * 3. Find a company according to data set
 * 4. Update company
 * 5. Verify company change
 *
 * @group Company
 * @ZephyrId MAGETWO-67909
 */
class UpdateCompanyEntityTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /** @var CompanyIndex */
    protected $companyIndex;

    /** @var CompanyEdit */
    protected $companyEdit;

    /**
     * Perform needed injections
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     */
    public function __inject(CompanyIndex $companyIndex, CompanyEdit $companyEdit)
    {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Update company
     *
     * @param Company $initialCompany
     * @param Company $company
     * @return array
     */
    public function test(Company $initialCompany, Company $company)
    {
        $initialCompany->persist();

        $filter = ['company_name' => $initialCompany->getCompanyName()];

        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getCompanyForm()->fill($company);
        $this->companyEdit->getFormPageActions()->save();

        return ['company' => $company];
    }
}
