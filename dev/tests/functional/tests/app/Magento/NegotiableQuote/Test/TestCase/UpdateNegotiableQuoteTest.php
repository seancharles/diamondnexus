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
 * 4. Customer update this quote.
 * 5. Admin update this quote.
 * 6. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId B2B-774, @ZephyrId B2B-662, @ZephyrId B2B-896, @ZephyrId B2B-1388, @ZephyrId MAGETWO-68699
 */
class UpdateNegotiableQuoteTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Test update quantity of products and expiration date on storefront and in admin panel.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
