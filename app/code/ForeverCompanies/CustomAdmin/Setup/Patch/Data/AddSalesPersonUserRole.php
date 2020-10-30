<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAdmin\Setup\Patch\Data;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddSalesPersonUserRole implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * RoleFactory
     *
     * @var RoleFactory
     */
    protected $roleFactory;

    /**
     * RulesFactory
     *
     * @var RulesFactory
     */
    protected $rulesFactory;

    /**
     * @var \Magento\Authorization\Model\ResourceModel\Rules
     */
    protected $rulesResource;

    /**
     * @var Role
     */
    protected $roleResource;

    /**
     * Init
     *
     * @param RoleFactory $roleFactory
     * @param RulesFactory $rulesFactory
     * @param Role $roleResource
     * @param \Magento\Authorization\Model\ResourceModel\Rules $rulesResource
     */
    public function __construct(
        RoleFactory $roleFactory, /* Instance of Role*/
        RulesFactory $rulesFactory, /* Instance of Rule */
        Role $roleResource,
        \Magento\Authorization\Model\ResourceModel\Rules $rulesResource
    )
    {
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
        $this->roleResource = $roleResource;
        $this->rulesResource = $rulesResource;
    }

    /**
     * {@inheritdoc}
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function apply()
    {
        $role=$this->roleFactory->create();
        $role->setData('role_name', 'Sales Person')
        ->setData('parent_id', 0) //set parent role id of your role
        ->setRoleType(Group::ROLE_TYPE)
            ->setUserType((string)UserContextInterface::USER_TYPE_ADMIN);
        $this->roleResource->save($role);
        $connection = $this->roleResource->getConnection();
        $mainTable = $this->roleResource->getMainTable();
        $select = $connection->select()->from($mainTable)->order('role_id desc')->limit(1);
        $roleRow = $connection->fetchRow($select);
        /* Now we set that which resources we allow to this role */
        $resource=[
            'Magento_Backend::admin',
            'Magento_Sales::sales',
            'Magento_Sales::sales_order',
            'Magento_Sales::create',
            'Magento_Sales::actions_view',
            'Magento_Sales::email',
            'Magento_Sales::reorder',
            'Magento_Sales::actions_edit',
            'Magento_Sales::cancel',
            'Magento_Sales::creditmemo',
            'Magento_Sales::sales_creditmemo',
            'Magento_Rma::magento_rma',
            'Magento_SalesArchive::orders',
            'Magento_SalesArchive::shipments',
            'Magento_SalesArchive::creditmemos',
            'Magento_AdvancedCheckout::magento_advancedcheckout',
            'Magento_AdvancedCheckout::view',
            'Magento_AdvancedCheckout::update',
            'Magento_Catalog::catalog',
            'Magento_Catalog::products',
            'Magento_PricePermissions::read_product_price',
            'Magento_Customer::customer',
            'Magento_Customer::manage',
            'Magento_Customer::reset_password',
            'Magento_Reward::reward_balance',
        ];
        /* Array of resource ids which we want to allow this role*/
        $rule = $this->rulesFactory->create();
        $rule->setRoleId($roleRow['role_id'])->setData('resources', $resource)->saveRel();
    }

    public function revert()
    {
        try {
            $connection = $this->roleResource->getConnection();
            $mainTable = $this->roleResource->getMainTable();
            $select = $connection->select()
                ->from(['main_table' => $mainTable])
                ->where('role_name = ?', 'Sales Person')
                ->deleteFromSelect('main_table');
        } catch (LocalizedException $e) {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '0.0.1';
    }
}
