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
 * 4. Customer adds a new address and sends the quote to Admin.
 * 5. Admin update this quote.
 * 6. Place order.
 * 7. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId B2B-1155
 */
class PlaceOrderWithoutShippingAddressTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Test place order without shipping address.
     */
    public function test()
    {
        $this->executeScenario();
    }
}
