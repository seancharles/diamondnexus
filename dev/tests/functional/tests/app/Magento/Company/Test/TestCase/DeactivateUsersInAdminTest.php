<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Preconditions:
 * 1. Create two customers without company.
 * 2. Create company.
 *
 * Steps:
 * 1. Login as admin
 * 2. Open first customer without company in the admin panel.
 * 3. Assign customer to the company.
 * 4. Open second customer without company in the admin panel.
 * 5. Assign second customer to the company.
 * 6. Navigate to the Stores>Companies
 * 7. Find a company according to data set
 * 8. Delete company
 * 9. Verify company absence
 * 10. Open recently assigned customer in the admin panel again.
 *
 * @group Company
 * @ZephyrId MAGETWO-68271
 */
class DeactivateUsersInAdminTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Companies Grid
     *
     * @var CompanyIndex
     */
    private $companyIndex;

    /**
     * Company edit page
     *
     * @var CompanyEdit
     */
    private $companyEdit;

    /**
     * Fixture factory
     *
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * Customers grid
     *
     * @var CustomerIndex $customerIndex
     */
    private $customerIndex;

    /**
     * Customer edit page
     *
     * @var CustomerIndexEdit $customerIndexEdit
     */
    private $customerIndexEdit;

    /**
     * Assign company to the specified customer
     *
     * @param Customer $customer
     * @param Customer $companyToAssign
     * @return void
     */
    private function assignCompanyToCustomer(Customer $customer, Customer $companyToAssign)
    {
        $filter['email'] = $customer->getEmail();
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEdit->getCustomerForm()->openTab('account_information');
        $this->customerIndexEdit->getCustomerForm()->fill($companyToAssign);
        $this->customerIndexEdit->getCompanyModalBlock()->acceptAlert();
        $this->customerIndexEdit->getPageActionsBlock()->save();
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Inactive status for customers after delete a company in Admin Panel
     *
     * @param Customer $firstCustomer
     * @param Customer $secondCustomer
     * @param Company $company
     * @return array
     */
    public function test(
        Customer $firstCustomer,
        Customer $secondCustomer,
        Company $company
    ) {

        $company->persist();

        $firstCustomer->persist();
        $secondCustomer->persist();
        $customerToFillForm = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default_with_company_association',
                'data' => [
                    'company_association' => $company->getCompanyName(),
                ],
            ]
        );
        $this->assignCompanyToCustomer($firstCustomer, $customerToFillForm);
        $this->assignCompanyToCustomer($secondCustomer, $customerToFillForm);
        $filter = ['company_name' => $company->getCompanyName()];
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getFormPageActions()->delete();
        $this->companyEdit->getModalBlock()->acceptAlert();
        return ['company' => $company, 'companyName' => '', 'customersCompany' => [$firstCustomer, $secondCustomer]];
    }
}
