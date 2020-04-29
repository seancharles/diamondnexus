<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertGrandTotalOrderReview;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Place order in one page checkout.
 */
class PlaceOrderStep extends \Magento\Checkout\Test\TestStep\PlaceOrderStep
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Price array.
     *
     * @var array
     */
    private $prices;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertGrandTotalOrderReview $assertGrandTotalOrderReview
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $prices
     * @param OrderInjectable|null $order
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertGrandTotalOrderReview $assertGrandTotalOrderReview,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        array $products = [],
        array $prices = [],
        OrderInjectable $order = null
    ) {
        parent::__construct(
            $checkoutOnepage,
            $assertGrandTotalOrderReview,
            $checkoutOnepageSuccess,
            $fixtureFactory,
            $products,
            $prices,
            $order
        );
        $this->prices = $prices;
        $this->checkoutOnepage = $checkoutOnepage;
    }

    /**
     * Place order after collecting order totals on review step.
     *
     * @return array
     */
    public function run(): array
    {
        $actualPrices = $this->getReviewTotals();
        $orderData = parent::run();
        $orderData['actualPrices'] = $actualPrices;
        return $orderData;
    }

    /**
     * Get review product prices.
     *
     * @return array
     */
    private function getReviewTotals(): array
    {
        $reviewBlock = $this->checkoutOnepage->getReviewBlock();
        $actualPrices = [
            'checkout_subtotal_excl_tax' => $reviewBlock->getSubtotalExclTax(),
            'checkout_subtotal_incl_tax' => $reviewBlock->getSubtotalInclTax(),
            'discount' => $reviewBlock->getDiscount(),
            'shipping_excl_tax' => $reviewBlock->getShippingExclTax(),
            'shipping_incl_tax' => $reviewBlock->getShippingInclTax(),
            'tax' => $reviewBlock->getTax(),
            'grand_total_excl_tax' => $reviewBlock->getGrandTotalExclTax(),
            'grand_total_incl_tax' => $reviewBlock->getGrandTotalInclTax()
        ];
        return $actualPrices;
    }
}
