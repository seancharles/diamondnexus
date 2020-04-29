<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check are customer group filter options correct.
 */
class AssertCustomerGroupFilterOptionsAreCorrect extends AbstractConstraint
{
    /**
     * Check are customer group filter options correct.
     *
     * @param CustomerIndex $customerIndex
     * @param array $customerGroupFilterOptions
     * @return void
     */
    public function processAssert(
        CustomerIndex $customerIndex,
        array $customerGroupFilterOptions
    ) {
        $customerIndex->open();
        $customerIndex->getGridHeader()->expandFiltersPanel();
        $filterOptions = $customerIndex->getGridHeader()->getCustomerGroupSharedCatalogOptions();

        \PHPUnit\Framework\Assert::assertTrue(
            $this->areFilterOptionsCorrect($customerGroupFilterOptions, $filterOptions),
            'Customer group filter options are incorrect.'
        );
    }

    /**
     * Check are all expected option in customer group filter.
     *
     * @param array $expectedOptions
     * @param array $filterOptions
     * @return bool
     */
    private function areFilterOptionsCorrect(array $expectedOptions, array $filterOptions)
    {
        $areOptionsCorrect = true;

        foreach ($expectedOptions as $expectedOption) {
            if (!in_array($expectedOption, $filterOptions)) {
                $areOptionsCorrect = false;
                break;
            }
        }

        return $areOptionsCorrect;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group filter options are correct.';
    }
}
