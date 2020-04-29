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
 *
 * Steps:
 * 1. Login as a customer to the SF.
 * 2. Add products to cart.
 * 3. Request a quote.
 * 4. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId B2B-2674, @ZephyrId B2B-774, @ZephyrId MAGETWO-68069, @ZephyrId MAGETWO-67878
 */
class RequestQuoteTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Test request negotiated quote.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
