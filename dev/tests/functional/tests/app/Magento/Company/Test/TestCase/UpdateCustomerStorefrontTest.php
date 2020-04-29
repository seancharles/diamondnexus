<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

/**
 * Test update entity on Storefront
 *
 * Test Flow:
 * 1. Register a new user
 * 2. Navigate to My Company
 * 3. Click on add entity link
 * 4. Fill out all data according to data set
 * 5. Save team
 * 6. Click on 'Edit Selected' link
 * 7. Fill out all data according to second data set
 * 8. Save entity
 * 9. Make assertions
 *
 * @group Company
 * @ZephyrId MAGETWO-67933
 */
class UpdateCustomerStorefrontTest extends AbstractUpdateEntityStorefrontTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */
}
