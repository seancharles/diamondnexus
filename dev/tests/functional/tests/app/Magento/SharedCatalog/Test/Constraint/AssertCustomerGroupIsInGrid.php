<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check is customer group in grid.
 */
class AssertCustomerGroupIsInGrid extends AbstractConstraint
{
    /**
     * Check that customer group is in grid.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param string $customerGroupCode
     * @return void
     */
    public function processAssert(
        CustomerGroupIndex $customerGroupIndex,
        $customerGroupCode
    ) {
        $customerGroupIndex->open();
        $filter = ['code' => $customerGroupCode];

        \PHPUnit\Framework\Assert::assertTrue(
            $customerGroupIndex->getCustomerGroupGrid()->isRowVisible($filter),
            'Customer group with code \'' . $customerGroupCode . '\' is absent in a customer groups grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is in grid.';
    }
}
