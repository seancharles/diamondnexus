<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Company\Api\Data\CompanyInterface;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company.
 *
 * Steps:
 * 1. Login as a customer to the SF.
 * 2. Perform assertions.
 *
 * @group Company
 * @ZephyrId MAGETWO-68251, @ZephyrId MAGETWO-68745
 */
class ChangeCompanyStatusTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Customer login page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->customerAccountLogin->open();
        $this->customerAccountLogin->getLoginBlock()->fill($customer);
        $this->customerAccountLogin->getLoginBlock()->submit();
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
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
    protected function createProducts(array $products)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Perform needed injections
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountLogin $customerAccountLogin
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Change company status and verify customer access
     *
     * @param string $companyDataset
     * @param Customer $customer
     * @param string $configData
     * @param array $addProductsToCart
     * @return array
     */
    public function test($companyDataset, Customer $customer, $configData = null, array $addProductsToCart = [])
    {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => $companyDataset,
                'data' => [
                    'email' => $customer->getEmail()

                ],
            ]
        );
        $company->persist();

        //Steps
        $this->loginCustomer($customer);
        if ($addProductsToCart) {
            $products = $this->createProducts($addProductsToCart);
            $this->addToCart($products);
        }

        return [
            'company' => $company
        ];
    }

    /**
     * Roll back config settings and log out customer
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
