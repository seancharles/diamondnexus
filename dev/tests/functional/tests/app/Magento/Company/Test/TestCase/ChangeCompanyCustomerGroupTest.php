<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Company\Test\Fixture\Company;

/**
 * Preconditions:
 * 1. Create company.
 * 2. Create customer group.
 *
 * Steps:
 * 1. Open company grid.
 * 2. Open company edit page.
 * 3. Assign company on customer group.
 * 4. Save company.
 *
 * @group Company
 * @ZephyrId MAGETWO-68598
 */
class ChangeCompanyCustomerGroupTest extends Injectable
{
    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Company grid page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyIndex
     */
    private $companyIndex;

    /**
     * Company edit page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyEdit
     */
    private $companyEdit;

    /**
     * Perform needed injections.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Change company status and verify customer access.
     *
     * @param Company $company
     * @param CustomerGroup $group
     * @return array
     */
    public function test(Company $company, CustomerGroup $group)
    {
        //Preconditions
        $company->persist();
        $group->persist();

        //Steps
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $this->companyEdit->getCompanyForm()->fillCustomerGroup($group->getCustomerGroupCode());
        if ($this->companyEdit->getModalBlock()->isVisible()) {
            $this->companyEdit->getModalBlock()->acceptAlert();
        }
        $this->companyEdit->getFormPageActions()->save();

        return [
            'companyName' => $company->getCompanyName(),
            'customerGroup' => $group->getCustomerGroupCode(),
        ];
    }
}
