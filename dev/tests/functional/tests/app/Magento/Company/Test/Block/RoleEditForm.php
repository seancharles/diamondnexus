<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Company\Test\Fixture\CompanyRole;

/**
 * Class RoleEditForm.
 */
class RoleEditForm extends Form
{
    /**
     * Xpath locator form title.
     *
     * @var string
     */
    private $formTitle = '//*[@id="role-edit-form"]/fieldset[2]/legend/span';

    /**
     * Css selector loader.
     *
     * @var string
     */
    protected $loader = '.loading-mask';

    /**
     * Css selector for Save Role button.
     *
     * @var string
     */
    protected $saveButton = '.action.save';

    /**
     * Css selector for Role Permissions tree.
     *
     * @var string
     */
    private $permissionsTree = '#role-tree';

    /**
     * Xpath locator form All Permissions checkbox.
     *
     * @var string
     */
    private $allPermissions = '//*[@id="Magento_Company::index_anchor"]';

    /**
     * All permissions tree label.
     *
     * @var string
     */
    private $allPermissionsTreeLabel = 'All';

    /**
     * Selected permissions.
     *
     * @var array
     */
    private $initialPermissions = [];

    /**
     * Focus out from form input.
     */
    public function focusOutFromInput()
    {
        $this->_rootElement->find($this->formTitle, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click Save Role button.
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->saveButton)->click();
    }

    /**
     * Add role.
     *
     * @param CompanyRole $role
     * @param bool $allPermissions
     * @return void
     */
    public function addRole(CompanyRole $role, $allPermissions = false)
    {
        $this->fill($role);
        $this->focusOutFromInput();
        if ($allPermissions) {
            $this->_rootElement->find($this->allPermissions, Locator::SELECTOR_XPATH)->click();
        }
        $this->submit();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Unselect all permissions in the tree.
     *
     * @return void
     */
    public function unselectAllPermissions()
    {
        $tree = $this->_rootElement->find(
            $this->permissionsTree,
            Locator::SELECTOR_CSS,
            \Magento\Company\Test\Element\RolePermissionTreeElement::class
        );
        $initialPermissions = $tree->getValue();
        if (in_array($this->allPermissionsTreeLabel, $initialPermissions)) {
            $this->_rootElement->find($this->allPermissions, Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Update role permissions.
     *
     * @param array $permissionsToCheck
     * @param array $permissionsToUnCheck
     * @return void
     */
    public function updateRolePermissions(array $permissionsToCheck = [], array $permissionsToUnCheck = [])
    {
        $this->changeRolePermissions($permissionsToCheck, $permissionsToUnCheck);
        $this->submit();
    }

    /**
     * Change role permissions.
     *
     * @param array $permissionsToCheck
     * @param array $permissionsToUnCheck
     * @return void
     */
    public function changeRolePermissions(array $permissionsToCheck = [], array $permissionsToUnCheck = [])
    {
        $tree = $this->_rootElement->find(
            $this->permissionsTree,
            Locator::SELECTOR_CSS,
            \Magento\Company\Test\Element\RolePermissionTreeElement::class
        );
        $initialPermissions = $tree->getValue();
        foreach ($permissionsToCheck as $permission) {
            if (!in_array($permission, $initialPermissions)) {
                $tree->setValue($permission);
            }
        }
        foreach ($permissionsToUnCheck as $permission) {
            if (in_array($permission, $initialPermissions)) {
                $tree->setValue($permission);
            }
        }
    }

    /**
     * Is permission selected in the role tree.
     *
     * @param string $permissionLabel
     * @param bool $loadInitialPermissions
     * @return bool
     */
    public function isPermissionSelected($permissionLabel, $loadInitialPermissions = true)
    {
        if ($loadInitialPermissions) {
            $tree = $this->_rootElement->find(
                $this->permissionsTree,
                Locator::SELECTOR_CSS,
                \Magento\Company\Test\Element\RolePermissionTreeElement::class
            );
            $this->initialPermissions = $tree->getValue();
        }

        return in_array($permissionLabel, $this->initialPermissions);
    }
}
