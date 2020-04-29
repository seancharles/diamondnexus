<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\RolesAndPermissionsIndex;
use Magento\Company\Test\Page\RoleEdit;

/**
 * Assert checkbox state after selecting another checkbox.
 */
class AssertRolePermissionCheckboxState extends AbstractConstraint
{
    /**
     * Role edit page.
     *
     * @var \Magento\Company\Test\Page\RoleEdit
     */
    private $roleEdit;

    /**
     * Legal address view permission.
     *
     * @var string
     */
    private $legalAddressViewPermission = 'All/Company Profile/Legal Address (View)';

    /**
     * Legal address edit permission.
     *
     * @var string
     */
    private $legalAddressEditPermission = 'All/Company Profile/Legal Address (View)/Edit';

    /**
     * Quotes permission.
     *
     * @var string
     */
    private $quotesPermission = 'All/Quotes';

    /**
     * Quotes View permission.
     *
     * @var string
     */
    private $quotesViewPermission = 'All/Quotes/View';

    /**
     * Quotes Request, Edit, Delete permission.
     *
     * @var string
     */
    private $quotesRequestEditDeletePermission = 'All/Quotes/View/Request, Edit, Delete';

    /**
     * Quotes checkout with quote permission.
     *
     * @var string
     */
    private $quotesCheckoutWithQuotePermission = 'All/Quotes/View/Checkout with quote';

    /**
     * Quotes view quotes of subordinate users.
     *
     * @var string
     */
    private $quotesViewQuotesOfSubordinateUsersPermission = 'All/Quotes/View/View quotes of subordinate users';

    /**
     * Process assert.
     *
     * @param RolesAndPermissionsIndex $rolesAndPermissionsIndex
     * @param RoleEdit $roleEdit
     * @param string $roleName
     */
    public function processAssert(
        RolesAndPermissionsIndex $rolesAndPermissionsIndex,
        RoleEdit $roleEdit,
        $roleName
    ) {
        $this->roleEdit = $roleEdit;
        $rolesAndPermissionsIndex->open();
        $rolesAndPermissionsIndex->getRolesGrid()->editRole($roleName);
        $this->roleEdit->getRoleEditForm()->unselectAllPermissions();
        $this->roleEdit->getRoleEditForm()->changeRolePermissions([$this->legalAddressEditPermission]);
        \PHPUnit\Framework\Assert::assertTrue(
            $this->roleEdit->getRoleEditForm()->isPermissionSelected($this->legalAddressViewPermission),
            'Legal Address (View) permission is not selected.'
        );
        $roleEdit->getRoleEditForm()->changeRolePermissions([], [$this->legalAddressViewPermission]);
        \PHPUnit\Framework\Assert::assertTrue(
            !$this->roleEdit->getRoleEditForm()->isPermissionSelected($this->legalAddressEditPermission),
            'Legal Address Edit permission is selected.'
        );
        $this->roleEdit->getRoleEditForm()->changeRolePermissions([$this->quotesPermission]);
        $this->roleEdit->getRoleEditForm()->changeRolePermissions([], [$this->quotesViewPermission]);
        \PHPUnit\Framework\Assert::assertTrue(
            !$this->roleEdit->getRoleEditForm()->isPermissionSelected($this->quotesRequestEditDeletePermission),
            'Quotes Request, Edit, Delete permission is selected.'
        );
        \PHPUnit\Framework\Assert::assertTrue(
            !$this->roleEdit->getRoleEditForm()->isPermissionSelected($this->quotesCheckoutWithQuotePermission, false),
            'Quotes Checkout with quote permission is selected.'
        );
        \PHPUnit\Framework\Assert::assertTrue(
            !$this->roleEdit->getRoleEditForm()
                ->isPermissionSelected($this->quotesViewQuotesOfSubordinateUsersPermission, false),
            'Quotes View quotes of subordinate users permission is selected.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Permissions state is correct.';
    }
}
