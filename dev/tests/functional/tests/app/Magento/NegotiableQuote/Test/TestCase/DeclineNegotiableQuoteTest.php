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
 * 4. Decline this quote.
 * 5. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68049
 */
class DeclineNegotiableQuoteTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Decline a negotiable quote in admin panel.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
