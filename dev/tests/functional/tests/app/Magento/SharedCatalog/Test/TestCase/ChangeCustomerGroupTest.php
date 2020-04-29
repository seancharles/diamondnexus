<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;

/**
 * Preconditions:
 * 1. Create two customers without company.
 * 2. Create company.
 * 3. Create shared catalog.
 *
 * Steps:
 * 1. Login as admin.
 * 2. Open customers without company in the admin panel.
 * 3. Assign customers to the company.
 * 4. Assign company to the shared catalog.
 * 5. Create new shared catalog.
 * 6. Assign company to the new shared catalog.
 * 7. Make assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68312
 */
class ChangeCustomerGroupTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

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
     * Company edit page.
     *
     * @var CompanyEdit
     */
    private $companyEdit;

    /**
     * Shared catalog index page.
     *
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * Shared catalog create page.
     *
     * @var SharedCatalogCreate $sharedCatalogCreate
     */
    private $sharedCatalogCreate;

    /**
     * Shared catalog company page.
     *
     * @var SharedCatalogCompany $sharedCatalogCompany
     */
    private $sharedCatalogCompany;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

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
     * Assign company to shared catalog.
     *
     * @param Company $company
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    private function assignCompanyToSharedCatalog(Company $company, SharedCatalog $sharedCatalog)
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $company->getCompanyName()]);
        $companyId = $this->sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $this->sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);
        if ($this->sharedCatalogCompany->getModalBlock()->isVisible()) {
            $this->sharedCatalogCompany->getModalBlock()->acceptAlert();
        }
        $this->sharedCatalogCompany->getPageActions()->save();
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate,
        SharedCatalogCompany $sharedCatalogCompany
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerIndex = $customerIndex;
        $this->companyEdit = $companyEdit;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->companyIndex = $companyIndex;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCreate = $sharedCatalogCreate;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
    }

    /**
     * Actions with Customers in Customer Group.
     *
     * @param array $customers
     * @param Customer $userWithoutCompany
     * @param Company $company
     * @param SharedCatalog $sharedCatalog
     * @param SharedCatalog $secondSharedCatalog
     * @param bool $newSharedCatalog
     * @param string|null $configData
     * @return array
     */
    public function test(
        array $customers,
        Customer $userWithoutCompany,
        Company $company,
        SharedCatalog $sharedCatalog,
        SharedCatalog $secondSharedCatalog,
        $newSharedCatalog,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $company->persist();
        $sharedCatalog->persist();
        $userWithoutCompany->persist();
        $customersList = [];
        foreach ($customers as $id => $customer) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => $customer['dataset']]);
            $customer->persist();
            $customersList[] = $customer;
            $assignItems[] = ['company_name' => $company->getCompanyName(), 'customer_email' => $customer->getEmail()];
        }

        // Steps:
        $this->assignCompanyToCustomers($assignItems);
        $this->assignCompanyToSharedCatalog($company, $sharedCatalog);
        $customerGroup = $sharedCatalog->getName();
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $this->companyEdit->getCompanyForm()->fillCustomerGroup($customerGroup);
        if ($this->companyEdit->getModalBlock()->isVisible()) {
            $this->companyEdit->getModalBlock()->acceptAlert();
        }
        $this->companyEdit->getFormPageActions()->save();
        $this->assignCompanyToCustomers(
            [
                [
                    'company_name' => $company->getCompanyName(),
                    'customer_email' => $userWithoutCompany->getEmail()
                ]
            ]
        );
        $customersList[] = $userWithoutCompany;
        if ($newSharedCatalog) {
            $this->sharedCatalogIndex->open();
            $this->sharedCatalogIndex->getGridPageActionBlock()->addNew();
            $form = $this->sharedCatalogCreate->getSharedCatalogForm();
            $form->waitForElementVisible($form->getTabSelector());
            $form->fill($secondSharedCatalog);
            $this->sharedCatalogCreate->getFormPageActions()->save();
            $this->assignCompanyToSharedCatalog($company, $secondSharedCatalog);
            $customerGroup = $secondSharedCatalog->getName();
        }

        return [
            'companyName' => $company->getCompanyName(),
            'customersCompany' => [$userWithoutCompany],
            'customer' => $userWithoutCompany,
            'customers' => $customersList,
            'customerGroup' => $customerGroup
        ];
    }

    /**
     * Roll back config settings.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
