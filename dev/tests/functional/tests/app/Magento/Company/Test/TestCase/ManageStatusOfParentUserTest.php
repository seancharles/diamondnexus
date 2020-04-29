<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\CompanyUsers;

/**
 * Preconditions:
 * 1. Create three customers
 * 2. Create company.
 * 3. Assign first customer as a company admin.
 * 4. Assign second customer as company user.
 * 5. Assign third customer as a child user of company user.
 *
 * Steps:
 * 1. Change parent company user status to inactive (or delete).
 * 2. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68319
 */
class ManageStatusOfParentUserTest extends AbstractCompanyTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Company page.
     *
     * @var CompanyPage
     */
    private $companyPage;

    /**
     * Company users.
     *
     * @var CompanyUsers
     */
    private $companyUsers;

    /**
     * Company user.
     *
     * @var Customer
     */
    private $user;

    /**
     * Inject.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     * @param CompanyUsers $companyUsers
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage,
        CompanyUsers $companyUsers
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
        $this->companyUsers = $companyUsers;
    }

    /**
     * Test.
     *
     * @param Customer $companyAdmin
     * @param Customer $parentCompanyUser
     * @param Customer $companyUser
     * @param array $userInactivePresenceInGrid
     * @param array $userActivePresenceInGrid
     * @param array $userInactivePresenceInCompanyTree
     * @param array $userActivePresenceInCompanyTree
     * @param bool $isChangeUserStatusToActive
     * @param array $steps
     * @param string $configData
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function test(
        Customer $companyAdmin,
        Customer $parentCompanyUser,
        Customer $companyUser,
        array $userInactivePresenceInGrid = [],
        array $userActivePresenceInGrid = [],
        array $userInactivePresenceInCompanyTree = [],
        array $userActivePresenceInCompanyTree = [],
        $isChangeUserStatusToActive = false,
        array $steps = [],
        $configData = null
    ) {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $companyAdmin->persist();
        $company = $this->createCompany($companyAdmin);
        $company->persist();
        $parentCompanyUserCustomer = $this->createCustomer($parentCompanyUser);
        $companyUserCustomer = $this->createCustomer($companyUser);
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->createUser($parentCompanyUserCustomer);
        $this->createUser($companyUserCustomer);
        $this->companyPage->getTree()
            ->assignChildUser($this->prepareCustomerName($parentCompanyUser), $this->prepareCustomerName($companyUser));
        $this->user = $parentCompanyUser;
        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $this->$classMethod();
        }

        return [
            'customer' => $parentCompanyUser,
            'childCustomer' => $companyUser,
            'userInactivePresenceInGrid' => $userInactivePresenceInGrid,
            'userActivePresenceInGrid' => $userActivePresenceInGrid,
            'userInactivePresenceInCompanyTree' => $userInactivePresenceInCompanyTree,
            'userActivePresenceInCompanyTree' => $userActivePresenceInCompanyTree,
            'isChangeUserStatusToActive' => $isChangeUserStatusToActive
        ];
    }

    /**
     * Set user inactive.
     *
     * @return void
     */
    protected function setUserStatusInactive()
    {
        $this->companyUsers->open();
        $this->companyUsers->getUsersGrid()->clickDeleteUser($this->user->getEmail());
        $this->companyUsers->getCustomerDeletePopup()->clickSetInactive();
    }

    /**
     * Delete user.
     *
     * @return void
     */
    protected function deleteUser()
    {
        $this->companyUsers->open();
        $this->companyUsers->getUsersGrid()->clickDeleteUser($this->user->getEmail());
        $this->companyUsers->getCustomerDeletePopup()->clickDelete();
    }

    /**
     * Create company customer.
     *
     * @param FixtureInterface $customer
     * @return void
     */
    private function createUser(FixtureInterface $customer)
    {
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->fill($customer);
        $this->companyPage->getCustomerPopup()->setJobTitle($customer->getJobTitle());
        $this->companyPage->getCustomerPopup()->setTelephone($customer->getTelephone());
        $this->companyPage->getCustomerPopup()->submit();
    }

    /**
     * Prepare customer full name.
     *
     * @param Customer $customer
     * @return string
     */
    private function prepareCustomerName(Customer $customer)
    {
        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }

    /**
     * Merge customer and company customer fixtures.
     *
     * @param Customer $customer
     * @return FixtureInterface
     */
    private function createCustomer(Customer $customer)
    {
        return $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastName(),
                    'email' => $customer->getEmail(),
                ],
            ]
        );
    }

    /**
     * Create company.
     *
     * @param Customer $customer
     * @return FixtureInterface
     */
    private function createCompany(Customer $customer)
    {
        return $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
    }
}
