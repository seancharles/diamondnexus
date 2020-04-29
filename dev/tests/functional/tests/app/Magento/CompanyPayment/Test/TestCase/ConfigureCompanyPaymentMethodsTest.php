<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyPayment\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Preconditions:
 * 1. Create product.
 * 2. Create company.
 *
 * Steps:
 * 1. Select Specific Payment Methods in B2B configuration.
 * 2. Open company.
 * 3. Select payment methods in Advanced Settings section.
 * 4. Add products to cart.
 * 5. Go to checkout/review .
 * 6. Assert that available payment methods match the expected ones.
 *
 * @group CompanyPayment
 * @ZephyrId MAGETWO-68323
 */
class ConfigureCompanyPaymentMethodsTest extends Injectable
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
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Perform needed injections.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test configure payment methods in different Admin Panel places.
     *
     * @param Customer $companyAdmin
     * @param array $productsData
     * @param array $checkout
     * @param string $expectedMethods
     * @param string $companyPayment
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        array $productsData,
        array $checkout,
        $expectedMethods,
        $companyPayment,
        $configData = null
    ) {
        // Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $companyAdmin->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_status',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $companyPaymentFixture = $this->fixtureFactory->createByCode('company', ['dataset' => $companyPayment]);
        $company->persist();
        $products = $this->prepareProducts($productsData);

        // Steps:
        $filter = ['company_name' => $company->getCompanyName()];
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getCompanyForm()->openSection('settings');
        $this->companyEdit->getCompanyForm()->fill($companyPaymentFixture);
        $this->companyEdit->getFormPageActions()->save();
        $this->loginCustomer($companyAdmin);
        $this->addToCart($products);
        $this->proceedToReviewAndPayments($checkout);

        return [
            'expectedPaymentMethods' => $expectedMethods
        ];
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
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Create products.
     *
     * @param array $productList
     * @return array
     */
    protected function prepareProducts(array $productList)
    {
        $addToCartStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        )->run();
        return $addToCartStep['products'];
    }

    /**
     * Add products to cart.
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        )->run();
    }

    /**
     * Proceed to review and payments step.
     *
     * @param array $checkout
     * @return void
     */
    protected function proceedToReviewAndPayments(array $checkout)
    {
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $checkout['shipping']]
        )->run();
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
