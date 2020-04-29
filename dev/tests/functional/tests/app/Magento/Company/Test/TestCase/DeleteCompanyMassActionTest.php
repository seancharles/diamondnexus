<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Preconditions:
 * 1. Create two customers without company.
 * 2. Create two companies.
 *
 * Steps:
 * 1. Login as admin
 * 2. Open first customer without company in the admin panel.
 * 3. Assign customer to the company.
 * 4. Open second customer without company in the admin panel.
 * 5. Assign second customer to the company.
 * 6. Navigate to the Stores>Companies
 * 7. Delete a company using mass action
 * 8. Open recently assigned customers in the admin panel again.
 *
 * @group Company
 * @ZephyrId MAGETWO-68284, @ZephyrId MAGETWO-67958
 */
class DeleteCompanyMassActionTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Companies Grid.
     *
     * @var CompanyIndex
     */
    private $companyIndex;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    private $fixtureFactory;

    /**
     * Customers grid.
     *
     * @var CustomerIndex $customerIndex
     */
    private $customerIndex;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit $customerIndexEdit
     */
    private $customerIndexEdit;

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    private $customerAccountLogin;

    /**
     * Assign company to the specified customers.
     *
     * @param array $items
     * @return void
     */
    private function assignCompanyToCustomers(array $items)
    {
        foreach ($items as $item) {
            $companyToAssign = $this->fixtureFactory->createByCode(
                'customer',
                [
                    'dataset' => 'default_with_company_association',
                    'data' => [
                        'company_association' => $item['company_name'],
                    ],
                ]
            );
            $filter['email'] = $item['customer_email'];
            $this->customerIndex->open();
            $this->customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
            $this->customerIndexEdit->getCustomerForm()->openTab('account_information');
            $this->customerIndexEdit->getCustomerForm()->fill($companyToAssign);
            $this->customerIndexEdit->getCompanyModalBlock()->acceptAlert();
            $this->customerIndexEdit->getPageActionsBlock()->save();
        }
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param ObjectManager $objectManager
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CompanyIndex $companyIndex
     * @param CustomerAccountLogin $customerAccountLogin
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        ObjectManager $objectManager,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        CompanyIndex $companyIndex,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->companyIndex = $companyIndex;
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Inactive status for customers after delete a company using mass action.
     *
     * @param array $customers
     * @param array $companies
     * @param string|null $configData
     * @return array
     */
    public function test(
        array $customers,
        array $companies,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $filter = [];
        $customersList = [];
        foreach ($customers as $id => $customer) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => $customer['dataset']]);
            $customer->persist();
            $company = $this->fixtureFactory->createByCode('company', ['dataset' => $companies[$id]['dataset']]);
            $company->persist();
            $filter[] = ['company_name' => $company->getCompanyName()];
            $customersList[] = $customer;
            $assignItems[] = ['company_name' => $company->getCompanyName(), 'customer_email' => $customer->getEmail()];
        }

        // Steps:
        $this->assignCompanyToCustomers($assignItems);
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->massaction($filter, 'Delete');
        $this->companyIndex->getMassActionDeletePopup()->cancelDelete();
        $this->companyIndex->getGrid()->massaction($filter, 'Delete');
        $this->companyIndex->getMassActionDeletePopup()->confirmDelete();

        return ['company' => '', 'companyName' => '', 'customersCompany' => $customersList, 'customer' => $customer];
    }
}
