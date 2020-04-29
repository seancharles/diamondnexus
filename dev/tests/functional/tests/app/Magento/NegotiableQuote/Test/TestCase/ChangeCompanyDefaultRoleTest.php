<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Catalog\Test\TestStep\CreateProductsStep;
use Magento\Checkout\Test\TestStep\AddProductsToTheCartStep;
use Magento\Config\Test\TestStep\SetupConfigurationStep;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\CompanyRole;
use Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\RolesAndPermissionsIndex;
use Magento\Company\Test\Page\RoleEdit;
use Magento\Company\Test\Page\CompanyUsers;
use Magento\Mtf\TestCase\Injectable;
use Magento\NegotiableQuote\Test\Page\NegotiableCheckoutCart as CheckoutCart;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
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
 * 6. Add products to cart.
 * 7. Request a quote.
 * 8. Make assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68351, @ZephyrId MAGETWO-68737
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeCompanyDefaultRoleTest extends Injectable
{
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
     * Cart page.
     *
     * @var CheckoutCart
     */
    private $cartPage;

    /**
     * Company index page.
     *
     * @var CompanyIndex
     */
    private $companyIndex;

    /**
     * Company edit page.
     *
     * @var CompanyEdit
     */
    private $companyEdit;

    /**
     * Page AdminCache.
     *
     * @var AdminCache
     */
    private $adminCache;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * Inject.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     * @param RolesAndPermissionsIndex $rolesAndPermissionsIndex
     * @param RoleEdit $roleEdit
     * @param CheckoutCart $cartPage
     * @param CompanyUsers $companyUsers
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param AdminCache $adminCache
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage,
        RolesAndPermissionsIndex $rolesAndPermissionsIndex,
        RoleEdit $roleEdit,
        CheckoutCart $cartPage,
        CompanyUsers $companyUsers,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        AdminCache $adminCache
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
        $this->rolesAndPermissionsIndex = $rolesAndPermissionsIndex;
        $this->roleEdit = $roleEdit;
        $this->cartPage = $cartPage;
        $this->companyUsers = $companyUsers;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->adminCache = $adminCache;
    }

    /**
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param CompanyRole $role
     * @param string $companyCredit
     * @param array $permissionsToUnCheck
     * @param array $productsData
     * @param array $quote
     * @param null $configData
     * @return void
     */
    public function test(
        Customer $companyAdmin,
        Customer $companyUser,
        CompanyRole $role,
        $companyCredit,
        array $permissionsToUnCheck,
        array $productsData,
        array $quote,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $companyAdmin->persist();
        $companyUser->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail()
                ],
            ]
        );
        $company->persist();
        $companyUserCustomer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'email' => $companyUser->getEmail(),
                ],
            ]
        );
        /** @var CompanyAttributes $userAttributes */
        $userAttributes = $this->fixtureFactory->createByCode(
            'company_attributes',
            [
                'data' => [
                    'company_id' => $company->getId(),
                    'customer_id' => $companyUser->getId(),
                    'job_title' => $companyUserCustomer->getJobTitle(),
                    'telephone' => $companyUserCustomer->getTelephone(),
                    'status' => 1
                ]
            ]
        );
        $userAttributes->persist();
        $products = $this->createProducts($productsData);
        $companyCreditFixture = $this->fixtureFactory->createByCode(
            'company',
            ['dataset' => $companyCredit]
        );
        //Steps:
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->addNewRole();
        $this->roleEdit->getRoleEditForm()->addRole($role, true);
        $this->companyUsers->open();
        $this->companyUsers->getUsersGrid()->clickEditUser($companyUserCustomer->getEmail());
        $this->companyUsers->getCustomerPopup()->changeRole($role->getRoleName());
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->editRole($role->getRoleName());
        $this->roleEdit->getRoleEditForm()->updateRolePermissions([], $permissionsToUnCheck);
        $this->logoutCustomer();
        $this->companyIndex->open();
        $filter = ['company_name' => $company->getCompanyName()];
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getCompanyForm()->openSection('company_credit');
        $this->companyEdit->getCompanyForm()->fill($companyCreditFixture);
        $this->companyEdit->getFormPageActions()->save();
        $this->loginCustomer($companyUser);
        $this->addToCart($products);
        $this->submitQuote($quote);
    }

    /**
     * Request a quote.
     *
     * @param array $quote
     * @return array
     */
    private function submitQuote(array $quote)
    {
        $this->cartPage->open();
        $this->cartPage->getRequestQuote()->requestQuote();
        $this->cartPage->getRequestQuotePopup()->fillForm($quote);
        $this->cartPage->getRequestQuotePopup()->submitQuote();
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            LoginCustomerOnFrontendStep::class,
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
            LogoutCustomerOnFrontendStep::class
        )->run();
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    private function addToCart(array $products)
    {
        $addToCartStep = $this->objectManager->create(
            AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    private function createProducts(array $products)
    {
        $createProductsStep = $this->objectManager->create(
            CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Reset config settings to default and logout customer.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
    }
}
