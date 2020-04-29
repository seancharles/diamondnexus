<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\CompanyUsers;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert company presence on grid with different statuses.
 */
class AssertUserPresenceAfterStatusChange extends AbstractConstraint
{
    /**
     * Company users page.
     *
     * @var CompanyUsers
     */
    private $companyUsers;

    /**
     * Company tree page.
     *
     * @var CompanyPage.
     */
    private $companyPage;

    /**
     * Process assert.
     *
     * @param CompanyUsers $companyUsers
     * @param CompanyPage $companyPage
     * @param Customer $customer
     * @param Customer $childCustomer
     * @param array $userInactivePresenceInGrid
     * @param array $userActivePresenceInGrid
     * @param array $userInactivePresenceInCompanyTree
     * @param array $userActivePresenceInCompanyTree
     * @param bool $isChangeUserStatusToActive
     */
    public function processAssert(
        CompanyUsers $companyUsers,
        CompanyPage $companyPage,
        Customer $customer,
        Customer $childCustomer,
        array $userInactivePresenceInGrid,
        array $userActivePresenceInGrid,
        array $userInactivePresenceInCompanyTree,
        array $userActivePresenceInCompanyTree,
        $isChangeUserStatusToActive
    ) {
        $this->companyUsers = $companyUsers;
        $this->companyPage = $companyPage;
        $this->companyUsers->open();
        $this->checkPresenceOfUserInGrid($customer, $userInactivePresenceInGrid);
        $this->companyPage->open();
        $this->checkPresenceOfUsersInCompanyTree($customer, $childCustomer, $userInactivePresenceInCompanyTree);

        if ($isChangeUserStatusToActive) {
            $this->companyUsers->open();
            $this->companyUsers->getUsersGrid()->clickShowInactiveUsers();
            $this->setUserStatusActive($customer);
            $this->companyUsers->getUsersGrid()->clickShowActiveUsers();
            $this->checkPresenceOfUserInGrid($customer, $userActivePresenceInGrid);
            $this->companyPage->open();
            $this->checkPresenceOfUsersInCompanyTree($customer, $childCustomer, $userActivePresenceInCompanyTree);
        }
    }

    /**
     * Assert presence in grid.
     *
     * @param Customer $customer
     * @param bool $expectedUserPresence
     */
    private function assertPresenceInGrid(Customer $customer, $expectedUserPresence)
    {
        $userPresence = $this->companyUsers->getUsersGrid()->isUserInGrid($customer->getEmail());

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedUserPresence,
            $userPresence,
            'User presence in grid is incorrect.'
        );
    }

    /**
     * Assert user presence in company tree.
     *
     * @param Customer $customer
     * @param bool $expectedUserPresence
     */
    public function assertPresenceInCompanyTree(Customer $customer, $expectedUserPresence)
    {
        $userPresence = $this->companyPage->getTree()->isUserInCompanyTree($this->prepareCustomerName($customer));

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedUserPresence,
            $userPresence,
            'User presence in company tree is incorrect.'
        );
    }

    /**
     * Check user presence in grid.
     *
     * @param Customer $customer
     * @param array $presenceInGrid
     */
    private function checkPresenceOfUserInGrid(Customer $customer, array $presenceInGrid)
    {
        $this->assertPresenceInGrid($customer, $presenceInGrid['active']);
        $this->companyUsers->getUsersGrid()->clickShowAllUsers();
        $this->assertPresenceInGrid($customer, $presenceInGrid['all']);
        $this->companyUsers->getUsersGrid()->clickShowInactiveUsers();
        $this->assertPresenceInGrid($customer, $presenceInGrid['inactive']);
    }

    /**
     * Set user status active.
     *
     * @param Customer $customer
     */
    private function setUserStatusActive(Customer $customer)
    {
        $this->companyUsers->getUsersGrid()->clickEditUser($customer->getEmail());
        $this->companyUsers->getCustomerPopup()->setUserStatusActive();
        $this->companyUsers->getCustomerPopup()->submit();
    }

    /**
     * Check presence of users in company tree.
     *
     * @param Customer $customer
     * @param Customer $childCustomer
     * @param array $expectedUserPresence
     * @return void
     */
    private function checkPresenceOfUsersInCompanyTree(
        Customer $customer,
        Customer $childCustomer,
        array $expectedUserPresence
    ) {
        $this->assertPresenceInCompanyTree($customer, $expectedUserPresence['parent']);
        $this->assertPresenceInCompanyTree($childCustomer, $expectedUserPresence['child']);
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
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'User presence in grid is correct.';
    }
}
