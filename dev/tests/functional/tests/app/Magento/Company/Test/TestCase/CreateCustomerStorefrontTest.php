<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

/**
 * Abstract test create entity on Storefront
 *
 * Test Flow:
 * 1. Register a new user
 * 2. Navigate to My Company
 * 3. Click on add entity link
 * 4. Fill out all data according to data set
 * 5. Save entity
 * 6. Make assertions
 *
 * @group Company
 * @ZephyrId MAGETWO-67932
 */
class CreateCustomerStorefrontTest extends AbstractCreateEntityStorefrontTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */
}
