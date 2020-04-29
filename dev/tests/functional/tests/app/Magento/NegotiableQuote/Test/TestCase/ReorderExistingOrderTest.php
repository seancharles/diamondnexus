<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Sales\Test\Page\OrderHistory;

/**
 * Preconditions:
 * 1. Apply configuration settings.
 * 2. Create customer.
 * 3. Create products.
 *
 * Steps:
 * 1. Login customer.
 * 2. Place order.
 * 3. Add product to cart.
 * 4. Reorder recently placed order.
 * 5. Replace items in cart.
 * 6. Proceed to checkout.
 * 7. Perform all assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68114, @ZephyrId MAGETWO-67925
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderExistingOrderTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Order history Page.
     *
     * @var OrderHistory
     */
    protected $orderHistory;

    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Configuration settings.
     *
     * @var string
     */
    protected $configData;

    /**
     * Checkout onepage page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Inject pages.
     *
     * @param ObjectManager $objectManager
     * @param OrderHistory $orderHistory
     * @param CheckoutCart $checkoutCart
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function __inject(
        ObjectManager $objectManager,
        OrderHistory $orderHistory,
        CheckoutCart $checkoutCart,
        CheckoutOnepage $checkoutOnepage
    ) {
        $this->objectManager = $objectManager;
        $this->orderHistory = $orderHistory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutOnepage = $checkoutOnepage;
    }

    /**
     * Place order.
     * @param array $shipping
     * @param array $payment
     * @return int
     */
    protected function placeOrder(array $shipping, array $payment)
    {
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            [
                'shipping' => $shipping
            ]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
            [
                'payment' => $payment
            ]
        )->run();
        $orderId = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return $orderId;
    }

    /**
     * Reorder existing order.
     *
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param array $productsList
     * @param array $productsForReorder
     * @param string $shippingAddress
     * @param array $shippingMethod
     * @param array $payment
     * @param string $configData
     * @return array
     */
    public function test(
        FixtureFactory $fixtureFactory,
        Customer $customer,
        array $productsList,
        array $productsForReorder,
        $shippingAddress,
        array $shippingMethod = [],
        array $payment = [],
        $configData = null
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $products = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productsList]
        )->run()['products'];
        $productsForReorder = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productsForReorder]
        )->run()['products'];
        $address = $fixtureFactory->createByCode('address', ['dataset' => $shippingAddress]);

        // Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        )->run();
        $orderId = $this->placeOrder($shippingMethod, $payment);
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $productsForReorder]
        )->run();
        $this->orderHistory->open();
        $this->orderHistory->getQuoteOrderHistoryBlock()->reorderQuote($orderId);
        $this->orderHistory->getReorderQuoteBlock()->replaceItems();
        $this->checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
        $this->checkoutOnepage->getShippingMethodBlock()->clickContinue();

        return [
            'shippingMethod' => $shippingMethod,
            'shippingAddress' => $address->getData(),
            'paymentMethod' => $payment['method']
        ];
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
