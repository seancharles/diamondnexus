<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Customer\Test\Fixture\Address;

/**
 * Preconditions:
 * 1. Create grouped product which contains 3 simple products.
 *
 * Steps:
 * 1. Open quick order page.
 * 2. Fill form with simple products contained in previously created grouped product.
 * 3. Place order.
 * 4. Perform all assertions.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-68263
 *
 */
class QuickAddInvisibleSimpleSkusTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /** @var QuickOrderPage */
    protected $quickOrderPage;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Perform needed injections
     *
     * @param QuickOrderPage $quickOrderPage
     */
    public function __inject(QuickOrderPage $quickOrderPage)
    {
        $this->quickOrderPage = $quickOrderPage;
    }

    /**
     * Test quick add invisible products to cart
     *
     * @param GroupedProduct $groupedProduct
     * @param Address $address
     * @param array $shipping
     * @param string $configData
     * @param array $payment
     * @return array
     */
    public function test(
        GroupedProduct $groupedProduct,
        Address $address,
        array $shipping,
        $configData = null,
        array $payment = []
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $groupedProduct->persist();
        $simpleProducts = $groupedProduct->getData('associated')['products'];
        $this->quickOrderPage->open();
        $this->quickOrderPage->getItems()->fill($simpleProducts);
        $this->quickOrderPage->getActions()->clickAddToCart();
        $orderId = $this->placeOrder($address, $shipping, $payment);

        return ['orderId' => $orderId];
    }

    /**
     * Place order
     *
     * @param Address $address
     * @param array $shipping
     * @param array $payment
     * @return int
     */
    protected function placeOrder(Address $address, array $shipping, array $payment = [])
    {
        $this->objectManager->create(\Magento\Checkout\Test\TestStep\ClickProceedToCheckoutStep::class)->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
            ['shippingAddress' => $address]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $shipping]
        )->run();
        if (!empty($payment)) {
            $this->objectManager->create(
                \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
                ['payment' => $payment]
            )->run();
        }
        $orderId = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return $orderId;
    }

    /**
     * Reset config settings to default
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
