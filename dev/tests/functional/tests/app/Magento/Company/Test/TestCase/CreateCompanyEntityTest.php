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
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Test Creation for CreateCompanyEntity.
 *
 * Test Flow:
 * 1. Login as admin.
 * 2. Navigate to the Stores>Companies.
 * 3. Click on 'Add New Company' button.
 * 4. Fill out all data according to data set.
 * 5. Save company.
 * 6. Verify created company.
 *
 * @group Company
 * @ZephyrId MAGETWO-67907, @ZephyrId MAGETWO-67926
 */
class CreateCompanyEntityTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /** @var CompanyIndex */
    private $companyIndex;

    /** @var CompanyEdit */
    private $companyEdit;

    /** @var FixtureFactory */
    private $fixtureFactory;

    /**
     * Perform needed injections.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(CompanyIndex $companyIndex, CompanyEdit $companyEdit, FixtureFactory $fixtureFactory)
    {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create company entity from Admin.
     *
     * @param Company $company
     * @return array
     */
    public function test(Company $company)
    {
        $this->companyIndex->open();
        $this->companyIndex->getGridPageActionBlock()->addNew();
        /** @var \Magento\Company\Test\Block\Adminhtml\CompanyForm $form */
        $form = $this->companyEdit->getCompanyForm();
        $form->setAdminEmail($company->getEmail());
        $form->fill($company);
        $this->companyEdit->getFormPageActions()->save();
        $this->companyIndex->open();
        $customer = $this->fixtureFactory->createByCode('customer', ['data' => $company->getData()]);

        return ['company' => $company, 'customer' => $customer];
    }
}
