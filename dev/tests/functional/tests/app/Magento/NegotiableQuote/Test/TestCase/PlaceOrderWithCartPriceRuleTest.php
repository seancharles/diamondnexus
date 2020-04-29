<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company.
 * 3. Create products.
 * 4. Create gift card and sales rule.
 *
 * Steps:
 * 1. Login as a customer to the SF.
 * 2. Add products to cart.
 * 3. Request a quote.
 * 4. Admin update this quote.
 * 5 Place order in one page checkout.
 * 6. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId B2B-1950
 */
class PlaceOrderWithCartPriceRuleTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Test place order with cart price rule.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
