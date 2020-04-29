<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shared catalog companies grid columns are correct.
 */
class AssertCompanyGridColumns extends AbstractConstraint
{
    /**
     * Assert that shared catalog companies grid columns are correct.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(\Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany)
    {
        $sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $columnTitles = $sharedCatalogCompanyGrid->getColumnsTitles();
        \PHPUnit\Framework\Assert::assertContains(
            'Assigned',
            $columnTitles,
            'Shared catalog companies grid columns are incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog companies grid columns are correct.';
    }
}
