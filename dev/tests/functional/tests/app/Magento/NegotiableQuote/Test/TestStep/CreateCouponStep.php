<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep;

/**
 * Creating sales rule coupon.
 */
class CreateCouponStep implements TestStepInterface
{
    /**
     * Sales Rule coupon.
     *
     * @var string
     */
    protected $coupon;

    /**
     * Factory for Fixture.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Delete all Sales Rule on backend.
     *
     * @var DeleteAllSalesRuleStep
     */
    protected $deleteAllSalesRule;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param DeleteAllSalesRuleStep $deleteRule
     * @param string $coupon
     */
    public function __construct(FixtureFactory $fixtureFactory, DeleteAllSalesRuleStep $deleteRule, $coupon = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->coupon = $coupon;
        $this->deleteAllSalesRule = $deleteRule;
    }

    /**
     * Create sales rule coupon.
     *
     * @return array
     */
    public function run()
    {
        $coupon = $this->fixtureFactory->createByCode('salesRule', ['dataset' => $this->coupon]);
        $coupon->persist();

        return ['coupon' => $coupon];
    }

    /**
     * Delete all sales rule.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->deleteAllSalesRule->run();
    }
}
