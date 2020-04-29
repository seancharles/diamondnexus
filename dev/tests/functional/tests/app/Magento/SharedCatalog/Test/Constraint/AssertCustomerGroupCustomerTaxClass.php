<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert customer group customer tax class.
 */
class AssertCustomerGroupCustomerTaxClass extends AbstractConstraint
{
    /**
     * Assert customer group customer tax class value.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param SharedCatalog $sharedCatalog
     * @param string $customerTaxClass
     * @return void
     */
    public function processAssert(
        CustomerGroupIndex $customerGroupIndex,
        SharedCatalog $sharedCatalog,
        $customerTaxClass
    ) {
        $customerGroupIndex->open();
        $filter = ['code' => $sharedCatalog->getName(), 'tax_class_id' => $customerTaxClass];
        $customerGroupIndex->getCustomerGroupGrid()->search($filter);

        \PHPUnit\Framework\Assert::assertTrue(
            $customerGroupIndex->getCustomerGroupGrid()->isRowVisible($filter),
            'Shared catalog has wrong Customer Tax Class value.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog has right Customer Tax Class value.';
    }
}
