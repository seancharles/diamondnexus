<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

/**
 * Test delete entity on Storefront
 *
 * Test Flow:
 * 1. Register a new user
 * 2. Navigate to My Company
 * 3. Click on add entity link
 * 4. Fill out all data according to data set
 * 5. Save team
 * 6. Click on 'Delete Selected' link
 * 7. Confirm deletion
 * 8. Make assertions
 *
 * @group Company
 * @ZephyrId MAGETWO-67936, @ZephyrId MAGETWO-68088
 */
class DeleteCustomerStorefrontTest extends AbstractDeleteEntityStorefrontTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */
}
