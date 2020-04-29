<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shared catalog companies grid columns panel is correct.
 */
class AssertCompanyGridColumnsPanel extends AbstractConstraint
{
    /**
     * Assert that shared catalog companies grid columns panel is correct.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
    ) {
        $sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $columns = $sharedCatalogCompanyGrid->getFieldsInColumnsPanel();

        \PHPUnit\Framework\Assert::assertContains(
            'Assigned',
            $columns,
            '\'Assigned\' column is absent in shared catalog companies grid columns panel.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog companies grid columns panel is correct.';
    }
}
