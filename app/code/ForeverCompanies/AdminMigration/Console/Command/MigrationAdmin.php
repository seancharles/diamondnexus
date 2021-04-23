<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\AdminMigration\Console\Command;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role;
use Magento\Authorization\Model\ResourceModel\Rules;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationAdmin extends Command
{
    const HOST = 'host';
    const DBNAME = 'dbname';
    const USERNAME = 'username';
    const PASSWORD = 'password';

    const RULE_MAPPING = [
        'admin/system/adminnotification' => 'Magento_AdminNotification::adminnotification',
        'admin/system/adminnotification/remove' => 'Magento_AdminNotification::adminnotification_remove',
        'admin/system/adminnotification/mark_as_read' => 'Magento_AdminNotification::mark_as_read',
        'admin/system/adminnotification/show_list' => 'Magento_AdminNotification::show_list',
        'admin/system/adminnotification/show_toolbar' => 'Magento_AdminNotification::show_toolbar',
        'admin/system/tools/backup' => 'Magento_Backup::backup',
        'admin/system/tools/backup/rollback' => 'Magento_Backup::rollback',
        'admin/report/shopcart' => 'Magento_Cart::cart',
        'admin/report/shopcart/product' => 'Magento_Cart::manage',
        'admin/catalog/attributes/attributes' => 'Magento_Catalog::attributes_attributes',
        'admin/catalog' => 'Magento_Catalog::catalog',
        'admin/system/config/cataloginventory' => 'Magento_Catalog::catalog_inventory',
        'admin/catalog/categories' => 'Magento_Catalog::categories',
        'admin/system/config/catalog' => 'Magento_Catalog::config_catalog',
        'admin/catalog/products' => 'Magento_Catalog::products',
        'admin/catalog/search' => 'Magento_Catalog::sets',
        'admin/catalog/update_attributes' => 'Magento_Catalog::update_attributes',
        'admin/promo' => 'Magento_CatalogRule::promo',
        'admin/promo/catalog' => 'Magento_CatalogRule::promo_catalog',
        'admin/system/config/checkout' => 'Magento_Checkout::checkout',
        'admin/sales/checkoutagreement' => 'Magento_CheckoutAgreements::checkoutagreement',
        'admin/cms/block' => 'Magento_Cms::block',
        'admin/system/config/cms' => 'Magento_Cms::config_cms',
        'admin/cms/media_gallery' => 'Magento_Cms::media_gallery',
        'admin/cms/page' => 'Magento_Cms::page',
        'admin/cms/page/delete' => 'Magento_Cms::page_delete',
        'admin/cms/page/save' => 'Magento_Cms::save',
        'admin/system/config/advanced' => 'Magento_Config::advanced',
        'admin/system/config' => 'Magento_Config::config',
        'admin/system/config/admin' => 'Magento_Config::config_admin',
        'admin/system/config/design' => 'Magento_Config::config_design',
        'admin/system/config/general' => 'Magento_Config::config_general',
        'admin/system/config/system' => 'Magento_Config::config_system',
        'admin/system/config/currency' => 'Magento_Config::currency',
        'admin/system/config/dev' => 'Magento_Config::dev',
        'admin/system/config/sendfriend' => 'Magento_Config::sendfriend',
        'admin/system/config/trans_email' => 'Magento_Config::trans_email',
        'admin/system/config/web' => 'Magento_Config::web',
        'admin/system/config/contacts' => 'Magento_Contact::contact',
        'admin/system/currency/rates' => 'Magento_CurrencySymbol::currency_rates',
        'admin/system/currency/symbols' => 'Magento_CurrencySymbol::symbols',
        'admin/system/currency' => 'Magento_CurrencySymbol::system_currency',
        'admin/system/config/customer' => 'Magento_Customer::config_customer',
        'admin/customer' => 'Magento_Customer::customer',
        'admin/customer/group' => 'Magento_Customer::group',
        'admin/customer/manage' => 'Magento_Customer::manage',
        'admin/customer/online' => 'Magento_Customer::online',
        'admin/system/config/downloadable' => 'Magento_Downloadable::downloadable',
        'admin/system/crypt_key' => 'Magento_EncryptionKey::crypt_key',
        'admin/system/config/google' => 'Magento_GoogleAnalytics::google',
        'admin/system/convert/export' => 'Magento_ImportExport::export',
        'admin/system/extensions' => 'Magento_Integration::extensions',
        'admin/newsletter' => 'Magento_Newsletter::newsletter',
        'admin/newsletter/problem' => 'Magento_Newsletter::problem',
        'admin/newsletter/queue' => 'Magento_Newsletter::queue',
        'admin/newsletter/subscriber' => 'Magento_Newsletter::subscriber',
        'admin/newsletter/template' => 'Magento_Newsletter::template',
        'admin/system/config/payment' => 'Magento_Payment::payment',
        'admin/system/config/payment_services' => 'Magento_Payment::payment_services',
        'admin/sales/billing_agreement/actions/manage' => 'Magento_Paypal::actions_manage',
        'admin/system/config/persistent' => 'Magento_Persistent::persistent',
        'admin/report/shopcart/abandoned' => 'Magento_Reports::abandoned',
        'admin/report/customers/accounts' => 'Magento_Reports::accounts',
        'admin/report/products/bestsellers' => 'Magento_Reports::bestsellers',
        'admin/report/salesroot/coupons' => 'Magento_Reports::coupons',
        'admin/report/customers' => 'Magento_Reports::customers',
        'admin/report/customers/orders' => 'Magento_Reports::customers_orders',
        'admin/report/products/downloads' => 'Magento_Reports::downloads',
        'admin/report/salesroot/invoiced' => 'Magento_Reports::invoiced',
        'admin/report/products/lowstock' => 'Magento_Reports::lowstock',
        'admin/report/products' => 'Magento_Reports::product',
        'admin/report/salesroot/refunded' => 'Magento_Reports::refunded',
        'admin/report' => 'Magento_Reports::report',
        'admin/report/salesroot' => 'Magento_Reports::reports',
        'admin/catalog/reviews_ratings/reviews/pending' => 'Magento_Review::pending',
        'admin/catalog/reviews_ratings' => 'Magento_Review::ratings',
        'admin/catalog/reviews_ratings/reviews/all' => 'Magento_Review::reviews_all',
        'admin/system/config/rss' => 'Magento_Rss::rss',
        'admin/sales/order/actions' => 'Magento_Sales::actions',
        'admin/sales/order/actions/edit' => 'Magento_Sales::actions_edit',
        'admin/sales/order/actions/view' => 'Magento_Sales::actions_view',
        'admin/sales/order/actions/cancel' => 'Magento_Sales::cancel',
        'admin/sales/order/actions/capture' => 'Magento_Sales::capture',
        'admin/sales/order/actions/comment' => 'Magento_Sales::comment',
        'admin/system/config/sales' => 'Magento_Sales::config_sales',
        'admin/sales/order/actions/create' => 'Magento_Sales::create',
        'admin/sales/order/actions/creditmemo' => 'Magento_Sales::creditmemo',
        'admin/sales/order/actions/email' => 'Magento_Sales::email',
        'admin/sales/order/actions/emails' => 'Magento_Sales::emails',
        'admin/system/config/promo' => 'Magento_SalesRule::config_promo',
        'admin/promo/quote' => 'Magento_SalesRule::quote',
        'admin/global_search' => 'Magento_Search::search',
        'admin/system/config/carriers' => 'Magento_Shipping::carriers',
        'admin/system/config/shipping' => 'Magento_Shipping::config_shipping',
        'admin/system/config/sitemap' => 'Magento_Sitemap::config_sitemap',
        'admin/catalog/sitemap' => 'Magento_Sitemap::sitemap',
        'admin/system/config/tax' => 'Magento_Tax::config_tax',
        'admin/sales/tax/import_export' => 'Magento_TaxImportExport::import_export',
        'admin/catalog/urlrewrite' => 'Magento_UrlRewrite::urlrewrite',
        'admin/system/acl' => 'Magento_User::acl',
        'admin/system/acl/roles' => 'Magento_User::acl_roles',
        'admin/system/acl/users' => 'Magento_User::acl_users',
        'admin/system/acl/locks' => 'Magento_User::locks',
        'admin/system/acl/variables' => 'Magento_Variable::variable',
        'admin/cms/widget_instance' => 'Magento_Widget::widget_instance',
        'admin/system/config/wishlist' => 'Magento_Wishlist::config_wishlist',
    ];

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var User
     */
    protected $userResource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RoleFactory
     */
    protected $roleFactory;

    /**
     * @var RulesFactory
     */
    protected $ruleFactory;

    /**
     * @var Role
     */
    protected $roleResource;

    /**
     * @var Rules
     */
    protected $ruleResource;

    protected $name = 'forevercompanies:migration-admin';

    /**
     * TransformAttributes constructor.
     * @param State $state
     * @param ConnectionFactory $connectionFactory
     * @param UserFactory $userFactory
     * @param User $userResource
     * @param ScopeConfigInterface $scopeConfig
     * @param RoleFactory $roleFactory
     * @param Role $roleResource
     * @param RulesFactory $rulesFactory
     * @param Rules $ruleResource
     */
    public function __construct(
        State $state,
        ConnectionFactory $connectionFactory,
        UserFactory $userFactory,
        User $userResource,
        ScopeConfigInterface $scopeConfig,
        RoleFactory $roleFactory,
        Role $roleResource,
        RulesFactory $rulesFactory,
        Rules $ruleResource
    ) {
        $this->state = $state;
        $this->connectionFactory = $connectionFactory;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
        $this->scopeConfig = $scopeConfig;
        $this->roleFactory = $roleFactory;
        $this->roleResource = $roleResource;
        $this->ruleFactory = $rulesFactory;
        $this->ruleResource = $ruleResource;
        parent::__construct($this->name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }
        $db = $this->initDb($input);
        $output->writeln('Migration admin groups');
        $this->migrateGroups($db, $output);
        $output->writeln('Success migrated! Execute bin/magento cache:flush');
    }

    /**
     * @param InputInterface $input
     * @return AdapterInterface
     */
    protected function initDb(InputInterface $input)
    {
        return $this->connectionFactory->create([
            'host' => $input->getOption(self::HOST) ?? '',
            'dbname' => $input->getOption(self::DBNAME) ?? '',
            'username' => $input->getOption(self::USERNAME) ?? '',
            'password' => $input->getOption(self::PASSWORD) ?? '',
            'active' => '1',
        ]);
    }

    /**
     * @param AdapterInterface $db
     * @param OutputInterface $output
     */
    protected function migrateGroups(AdapterInterface $db, OutputInterface $output)
    {
        $roleTable = $this->scopeConfig->getValue('forevercompanies_adminmigration/tables/role');
        $ruleTable = $this->scopeConfig->getValue('forevercompanies_adminmigration/tables/rule');
        $userTable = $this->scopeConfig->getValue('forevercompanies_adminmigration/tables/user');
        $selectForDelete = $db->select()->from('admin_user');
        $deleteUsers = $db->select()->getConnection()->fetchAll($selectForDelete);
        foreach ($deleteUsers as $deleteUser) {
            $userModel = $this->userFactory->create();
            $this->userResource->load($userModel, $deleteUser['user_id']);
            try {
                $this->userResource->delete($userModel);
            } catch (LocalizedException $e) {
                $output->writeln('Can\'t delete user ' . $deleteUser['email']);
            }
        }
        $db->delete('authorization_rule', 'role_id > 1');
        $db->delete('authorization_role', 'role_id > 1');
        $select = $db->select()->from($roleTable)
            ->where('role_type = ?', 'G');
        $groups = $db->select()->getConnection()->fetchAll($select);
        $roleNames = [];
        foreach ($groups as $group) {
            if ($group['role_name'] == "Administrators") {
                $roleNames[$group['role_id']] = $group['role_name'];
                continue;
            }
            $output->writeln('Migrated group "' . $group['role_name'] . '"');
            $role = $this->roleFactory->create();
            $role->setData('name', $group['role_name'])
                ->setData('parent_id', 0)
                ->setRoleType(Group::ROLE_TYPE)
                ->setUserType((string)UserContextInterface::USER_TYPE_ADMIN);
            $roleNames[$group['role_id']] = $group['role_name'];
            try {
                $this->roleResource->save($role);
            } catch (AlreadyExistsException $e) {
                continue;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
        foreach ($roleNames as $oldRoleId => $roleName) {
            $output->writeln('Migrated all users from group "' . $roleName . '"');
            try {
                $selectedRole = $this->roleResource->getConnection()->select()
                    ->from($this->roleResource->getMainTable())
                    ->where('role_name = ?', $roleName);
                $roleId = $this->roleResource->getConnection()->fetchRow($selectedRole)['role_id'];
                $newRoleIds[$roleName] = $roleId;
                $select = $db->select()->from(['role' => $roleTable])
                    ->joinInner(['rule' => $ruleTable], 'rule.role_id = role.role_id')
                    ->where('role.role_name = ?', $roleName)
                    ->where('rule.permission = ?', 'allow');
                $resource = [];
                foreach ($db->fetchAll($select) as $rule) {
                    if (isset(self::RULE_MAPPING[$rule['resource_id']])) {
                        $resource[] = self::RULE_MAPPING[$rule['resource_id']];
                    }
                }
                $this->ruleFactory->create()->setRoleId($roleId)->setData('resources', $resource)->saveRel();
                $selectUsers = $db->select()->from(['user' => $userTable])
                    ->joinInner(['role' => $roleTable], 'user.user_id = role.user_id')
                    ->where('role.parent_id = ?', $oldRoleId);
                foreach ($db->fetchAll($selectUsers) as $user) {
                    $adminInfo = [
                        'username' => $user['username'],
                        'firstname' => $user['firstname'],
                        'lastname' => $user['lastname'],
                        'email' => $user['email'],
                        'password' => $user['password'] . 'I3GS',
                        'interface_locale' => 'en_US',
                        'is_active' => 1,
                        'role_id' => $roleId
                    ];
                    $userModel = $this->userFactory->create();
                    $userModel->setData($adminInfo);
                    try {
                        $this->userResource->save($userModel);
                        $savedUser = $this->userResource->loadByUsername($user['username']);
                        $bind = ['user_id' => $user['user_id']];
                        $db->delete('magento_logging_event', 'user_id = ' . $savedUser['user_id']);
                        $db->update('admin_user', $bind, 'user_id = ' . $savedUser['user_id']);
                        $db->update('authorization_role', $bind, 'user_id = ' . $savedUser['user_id']);
                        $this->userResource->getConnection()->update(
                            $this->userResource->getTable('admin_passwords'),
                            ['password_hash' => $user['password']],
                            ['user_id' => $savedUser['user_id']]
                        );
                        $lastRow = $db->select()->from('admin_user')->order('user_id desc')->limit(1);
                        $id = $db->fetchRow($lastRow);
                        $nextId = $id['user_id'] + 1;
                        /** @codingStandardsIgnoreStart */
                        $db->query('ALTER TABLE admin_user AUTO_INCREMENT = ' . $nextId);
                        /** @codingStandardsIgnoreSEnd */
                    } catch (AlreadyExistsException $e) {
                        $output->writeln('Can\'t save exists user with username ' . $user['username']);
                    } catch (\Exception $e) {
                        $output->writeln('Can\'t save admin ' . $user['username'] . ':' . $e->getMessage());
                    }
                }
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
            }
        }
        $db->insert(
            'authorization_rule',
            ['role_id' => 1, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow']
        );
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription('Migration admin users from M1 db to M2');
        $this->addOption(
            self::HOST,
            null,
            InputOption::VALUE_REQUIRED,
            'Host'
        );
        $this->addOption(
            self::DBNAME,
            'd',
            InputOption::VALUE_REQUIRED,
            'Database name'
        );
        $this->addOption(
            self::USERNAME,
            'u',
            InputOption::VALUE_REQUIRED,
            'Username'
        );
        $this->addOption(
            self::PASSWORD,
            'p',
            InputOption::VALUE_OPTIONAL,
            'Password'
        );

        parent::configure();
    }
}
