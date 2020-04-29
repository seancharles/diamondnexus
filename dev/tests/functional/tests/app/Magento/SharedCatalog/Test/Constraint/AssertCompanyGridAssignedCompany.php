<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that company assigned to current Shared Catalog is correctly displayed on Shared Catalog Companies Grid.
 */
class AssertCompanyGridAssignedCompany extends AbstractConstraint
{
    /**
     * Assert that correct company is assigned to current shared catalog.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @param string $expectedAssignedCompanyName
     * @return void
     */
    public function processAssert(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany,
        $expectedAssignedCompanyName
    ) {
        $sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $assignedCompanyId = $sharedCatalogCompanyGrid->getFirstItemId();
        $assignedCompanyName = $sharedCatalogCompanyGrid->getColumnValue($assignedCompanyId, 'Company');

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedAssignedCompanyName,
            $assignedCompanyName,
            'Assigned company for current shared catalog at shared catalog companies grid is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assigned company for current shared catalog at shared catalog companies grid is correct.';
    }
}
