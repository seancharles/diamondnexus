<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Test reactivate customer
 *
 * Test Flow:
 * 1. Register a new user
 * 2. Navigate to My Company
 * 3. Click on add entity link
 * 4. Fill out all data according to data set
 * 5. Save team
 * 6. Click on 'Delete Selected' link
 * 7. Go to admin
 * 8. Re-activate customer
 * 9. Make assertions
 *
 * @group Company
 * @ZephyrId MAGETWO-68089
 */
class ReActivateCustomerStorefrontTest extends DeleteCustomerStorefrontTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Test reactivate customer
     *
     * @param Customer $customer
     * @param string $entity
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, $entity, $configData = null)
    {
        $return = parent::test($customer, $entity, $configData);

        $customer = $return['entity']->getData();

        $nameParts = ['prefix', 'firstname', 'middlename', 'lastname', 'suffix'];
        $filteredNameParts = array_intersect_key($customer, array_flip($nameParts));
        $name = implode(' ', $filteredNameParts);

        $filter = [
            'name' => $name,
            'email' => $customer['email'],
        ];

        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->resetFilter();
        $this->customerIndex->getCustomerGridBlock()->searchAndSelect($filter);
        $this->customerIndex->getCustomerGridBlock()->selectAction('Set Active');

        return $return;
    }
}
