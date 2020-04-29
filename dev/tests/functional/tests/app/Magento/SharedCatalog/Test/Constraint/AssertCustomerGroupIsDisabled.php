<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Assert that customer group is disabled.
 */
class AssertCustomerGroupIsDisabled extends AbstractConstraint
{
    /**
     * Assert that customer group is disabled.
     *
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param array $customers
     */
    public function processAssert(
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        array $customers
    ) {
        foreach ($customers as $customer) {
            $filter = [
                'email' => $customer->getEmail(),
            ];
            $customerIndex->open();
            $customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
            \PHPUnit\Framework\Assert::assertTrue(
                $customerIndexEdit->getCustomerView()->getCustomerGroup()->isDisabled(),
                'Customer group is not disabled.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is disabled.';
    }
}
