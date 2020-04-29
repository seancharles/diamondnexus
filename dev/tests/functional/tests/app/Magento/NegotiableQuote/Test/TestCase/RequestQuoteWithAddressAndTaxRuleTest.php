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
 * 4. Admin update this quote.
 * 5. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68116
 */
class RequestQuoteWithAddressAndTaxRuleTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Request quote with address and tax rule.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
