<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shared catalog companies grid filters are correct.
 */
class AssertCompanyGridFilters extends AbstractConstraint
{
    /**
     * Assert that shared catalog companies grid filters are correct.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
    ) {
        $sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $filtersTitles = $sharedCatalogCompanyGrid->getFiltersTitles();
        \PHPUnit\Framework\Assert::assertContains(
            'Assigned',
            $filtersTitles,
            'Shared catalog companies grid filter titles are incorrect'
        );

        $assignFilterOptions = $sharedCatalogCompanyGrid->getAssignFilterOptions();
        \PHPUnit\Framework\Assert::assertContains(
            'Yes',
            $assignFilterOptions,
            'Shared catalog companies grid filter options for \'Assigned\' filter are incorrect'
        );
        \PHPUnit\Framework\Assert::assertContains(
            'No',
            $assignFilterOptions,
            'Shared catalog companies grid filter options for \'Assigned\' filter are incorrect'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog companies grid filters are correct.';
    }
}
