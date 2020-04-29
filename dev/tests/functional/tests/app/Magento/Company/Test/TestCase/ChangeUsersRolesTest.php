<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\CompanyRole;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\RolesAndPermissionsIndex;
use Magento\Company\Test\Page\RoleEdit;
use Magento\Company\Test\Page\CompanyUsers;
use Magento\Company\Test\Fixture\CompanyAttributes;

/**
 * Preconditions:
 * 1. Create two customers
 * 2. Create company.
 * 3. Assign first customer as a company admin.
 * 4. Assign second customer as company user.
 *
 * Steps:
 * 1. Add new role.
 * 2. Go to company users page and assign created role to company user.
 * 3. Go to My Company page.
 * 4. Update role permissions.
 * 5. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68320, @ZephyrId MAGETWO-68358
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeUsersRolesTest extends AbstractCompanyTest
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
     * Roles and permissions index page.
     *
     * @var RolesAndPermissionsIndex
     */
    private $rolesAndPermissionsIndex;

    /**
     * Role edit page.
     *
     * @var RoleEdit
     */
    private $roleEdit;

    /**
     * Company users.
     *
     * @var CompanyUsers
     */
    private $companyUsers;

    /**
     * View roles and permissions link on role edit page.
     *
     * @var string|null
     */
    private $roleEditViewRolesAndPermissionsLinkText;

    /**
     * Role name.
     *
     * @var string
     */
    private $roleName;

    /**
     * Inject.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     * @param RolesAndPermissionsIndex $rolesAndPermissionsIndex
     * @param RoleEdit $roleEdit
     * @param CompanyUsers $companyUsers
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage,
        RolesAndPermissionsIndex $rolesAndPermissionsIndex,
        RoleEdit $roleEdit,
        CompanyUsers $companyUsers
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
        $this->rolesAndPermissionsIndex = $rolesAndPermissionsIndex;
        $this->roleEdit = $roleEdit;
        $this->companyUsers = $companyUsers;
    }

    /**
     * Test.
     *
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param CompanyRole $role
     * @param bool $linkPresenceInMenu
     * @param string $rolesAndPermissionsLinkText
     * @param string|null $companyAdminRole
     * @param string|null $roleEditViewRolesAndPermissionsLinkText
     * @param array $steps
     * @param null $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        Customer $companyUser,
        CompanyRole $role,
        $linkPresenceInMenu,
        $rolesAndPermissionsLinkText,
        $roleEditViewRolesAndPermissionsLinkText = null,
        $companyAdminRole = null,
        array $steps = [],
        $configData = null
    ) {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $companyAdmin->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $company->persist();
        $companyUser->persist();
        //Creating company relation.
        $companyUserCustomer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'email' => $companyUser->getEmail(),
                    'firstname' => $companyUser->getFirstname(),
                    'lastname' => $companyUser->getLastname()
                ],
            ]
        );
        /** @var CompanyAttributes $companyAttributes */
        $companyAttributes = $this->fixtureFactory->createByCode(
            'company_attributes',
            [
                'data' => [
                    'customer_id' => $companyUser->getId(),
                    'company_id' => $company->getId(),
                    'job_title' => $companyUserCustomer->getJobTitle(),
                    'telephone' => $companyUserCustomer->getTelephone(),
                    'status' => 1
                ]
            ]
        );
        $companyAttributes->persist();
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->addCustomer($companyUserCustomer);
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->addNewRole();
        $this->roleEdit->getRoleEditForm()->addRole($role);
        $this->companyUsers->open();
        $this->companyUsers->getUsersGrid()->clickEditUser($companyUserCustomer->getEmail());
        $this->companyUsers->getCustomerPopup()->changeRole($role->getRoleName());
        $this->roleEditViewRolesAndPermissionsLinkText = $roleEditViewRolesAndPermissionsLinkText;
        $this->roleName = $role->getRoleName();

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $this->$classMethod();
        }

        return [
            'companyUserRole' => $role->getRoleName(),
            'companyAdminRole' => $companyAdminRole,
            'companyUser' => $companyUser,
            'linkPresenceInMenu' => $linkPresenceInMenu,
            'rolesAndPermissionsLinkText' => $rolesAndPermissionsLinkText
        ];
    }

    /**
     * Update role permissions.
     *
     * @return void
     */
    protected function updateRolePermissions()
    {
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->editRole($this->roleName);
        $this->roleEdit->getRoleEditForm()->updateRolePermissions([$this->roleEditViewRolesAndPermissionsLinkText]);
    }
}
