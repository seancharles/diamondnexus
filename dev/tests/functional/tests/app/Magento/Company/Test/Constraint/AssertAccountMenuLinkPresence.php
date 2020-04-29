<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\CompanyUsers;

/**
 * Assert company presence on grid with different statuses.
 */
class AssertAccountMenuLinkPresence extends AbstractConstraint
{
    /**
     * Company users page.
     *
     * @var CompanyUsers
     */
    private $companyUsers;

    /**
     * Assert that link is present in the menu.
     *
     * @param CompanyUsers $companyUsers
     * @param Customer $companyUser
     * @param bool $linkPresenceInMenu
     * @param string $rolesAndPermissionsLinkText
     */
    public function processAssert(
        CompanyUsers $companyUsers,
        Customer $companyUser,
        $linkPresenceInMenu,
        $rolesAndPermissionsLinkText
    ) {
        $this->companyUsers = $companyUsers;
        $this->logoutCustomer();
        $this->loginCustomer($companyUser);

        \PHPUnit\Framework\Assert::assertEquals(
            $linkPresenceInMenu,
            $this->companyUsers->getAccountMenu()->isMenuLinkPresented($rolesAndPermissionsLinkText),
            'Link presence in menu is not correct.'
        );
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    private function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    private function logoutCustomer()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Link presence in menu is correct.';
    }
}
