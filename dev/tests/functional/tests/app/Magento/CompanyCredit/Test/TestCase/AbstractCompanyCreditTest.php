<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Base class for CompanyCredit module test cases.
 */
abstract class AbstractCompanyCreditTest extends Injectable
{
    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $loginCustomerOnFrontendStep = $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        );
        $loginCustomerOnFrontendStep->run();
    }

    /**
     * Create products.
     *
     * @param array $productList
     * @return array
     */
    protected function prepareProducts(array $productList)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $createProductsStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart.
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
     * Place order.
     *
     * @param array $checkout
     * @return int
     */
    protected function placeOrder(array $checkout)
    {
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $checkout['shipping']]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
            [
                'payment' => $checkout['payment'],
            ]
        )->run();

        return $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];
    }

    /**
     * Create invoice.
     *
     * @param \Magento\Sales\Test\Fixture\OrderInjectable $order
     * @param $products
     * @param $cart
     * @return array
     */
    protected function createInvoice(\Magento\Sales\Test\Fixture\OrderInjectable $order, $products, $cart)
    {
        $createInvoiceStep = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            [
                'products' => $products,
                'order' => $order,
                'cart' => $cart
            ]
        );
        return $createInvoiceStep->run();
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
