<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Company\Test\Page\Adminhtml\ConfigCustomerSetup;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Assert that customer has correct group
 */
class AssertCustomerGroupIsCorrect extends AbstractConstraint
{
    /**
     * Assert that customer has correct group
     *
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param ConfigCustomerSetup $configCustomerSetup,
     * @param Customer $customer
     * @param string|null $customerGroup
     */
    public function processAssert(
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        ConfigCustomerSetup $configCustomerSetup,
        Customer $customer,
        $customerGroup = null
    ) {
        if ($customerGroup === null) {
            $configCustomerSetup->open();
            $customerGroup = $configCustomerSetup->getAccountOptions()->getDefaultGroup();
        }
        $filter = [
            'email' => $customer->getEmail(),
        ];
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen($filter);

        \PHPUnit\Framework\Assert::assertEquals(
            $customerGroup,
            $customerIndexEdit->getCustomerForm()->getData($customer)['group_id'],
            'Customer group is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is correct.';
    }
}
