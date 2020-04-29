<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shared catalog companies grid available actions are correct on first grid load.
 */
class AssertCompanyGridAvailableActionsOnLoad extends AbstractConstraint
{
    /**
     * Assert that shared catalog companies grid available actions are correct on first grid load.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(\Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany)
    {
        $sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $id = $sharedCatalogCompanyGrid->getFirstItemId();
        $actionColumnValue = $sharedCatalogCompanyGrid->getColumnValue($id, 'Action');

        \PHPUnit\Framework\Assert::assertContains(
            'Unassign',
            $actionColumnValue,
            'Shared catalog companies grid available actions are not correct on first grid load.'
        );
        \PHPUnit\Framework\Assert::assertNotContains(
            'Assign',
            $actionColumnValue,
            'Shared catalog companies grid available actions are not correct on first grid load.'
        );

        $firstActiveFilter = $sharedCatalogCompanyGrid->getFirstActiveFilter();
        \PHPUnit\Framework\Assert::assertEquals(
            'Assigned:',
            $firstActiveFilter,
            'Shared catalog companies grid default filter is not correct on first grid load.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog companies grid available actions are correct on first grid load.';
    }
}
