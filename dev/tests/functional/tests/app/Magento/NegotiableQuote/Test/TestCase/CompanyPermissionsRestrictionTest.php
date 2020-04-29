<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Catalog\Test\TestStep\CreateProductsStep;
use Magento\Checkout\Test\TestStep\AddProductsToTheCartStep;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
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
 * @ZephyrId MAGETWO-68429, MAGETWO-68428
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyPermissionsRestrictionTest extends Injectable
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
     * Cms index page.
     *
     * @var CmsPageIndex
     */
    private $cmsPageIndex;

    /**
     * Cms page.
     *
     * @var CmsPageNew
     */
    private $cmsPage;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param CompanyPage $companyPage
     * @param RolesAndPermissionsIndex $rolesAndPermissionsIndex
     * @param RoleEdit $roleEdit
     * @param CheckoutCart $cartPage
     * @param CompanyUsers $companyUsers
     * @param CmsPageIndex $cmsPageIndex
     * @param CmsPageNew $cmsPage
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyPage $companyPage,
        RolesAndPermissionsIndex $rolesAndPermissionsIndex,
        RoleEdit $roleEdit,
        CheckoutCart $cartPage,
        CompanyUsers $companyUsers,
        CmsPageIndex $cmsPageIndex,
        CmsPageNew $cmsPage
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyPage = $companyPage;
        $this->rolesAndPermissionsIndex = $rolesAndPermissionsIndex;
        $this->roleEdit = $roleEdit;
        $this->cartPage = $cartPage;
        $this->companyUsers = $companyUsers;
        $this->cmsPageIndex = $cmsPageIndex;
        $this->cmsPage = $cmsPage;
    }

    /**
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param CompanyRole $role
     * @param CmsPage $deniedPage
     * @param array $permissionsToUnCheck
     * @param array $productsData
     * @param array $quote
     * @param null $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        Customer $companyUser,
        CompanyRole $role,
        CmsPage $deniedPage,
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
        /** @var CompanyAttributes $companyUserAttributes */
        $companyUserAttributes = $this->fixtureFactory->createByCode(
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
        $companyUserAttributes->persist();
        $products = $this->createProducts($productsData);

        //Steps:
        $this->cmsPageIndex->open();
        $this->cmsPageIndex->getCmsPageGridBlock()->searchAndOpen(['identifier' => 'access-denied-page']);
        $this->cmsPage->getPageForm()->fill($deniedPage);
        $this->cmsPage->getPageMainActions()->save();

        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->addNewRole();
        $this->roleEdit->getRoleEditForm()->addRole($role, true);
        $this->companyUsers->open();
        $this->companyUsers->getUsersGrid()->clickEditUser($companyUserCustomer->getEmail());
        $this->companyUsers->getCustomerPopup()->changeRole($role->getRoleName());
        $this->logoutCustomer();

        $this->loginCustomer($companyUser);
        $this->addToCart($products);
        $this->submitQuote($quote);
        $this->logoutCustomer();

        $this->loginCustomer($companyAdmin);
        $this->rolesAndPermissionsIndex->open();
        $this->rolesAndPermissionsIndex->getRolesGrid()->editRole($role->getRoleName());
        $this->roleEdit->getRoleEditForm()->updateRolePermissions([], $permissionsToUnCheck);
        $this->logoutCustomer();

        $this->loginCustomer($companyUser);

        return [
            'roleName' => $role->getRoleName()
        ];
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
     * Submit a quote.
     *
     * @param array $quote
     * @return void
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
