<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that sorting is correct at shared catalog companies grid.
 */
class AssertCompanyGridSort extends AbstractConstraint
{
    /**
     * @var \Magento\SharedCatalog\Test\Block\Adminhtml\CompanyGrid
     */
    private $sharedCatalogCompanyGrid;

    /**
     * Assert that sorting is correct at shared catalog companies grid.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
    ) {
        $this->sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $this->sharedCatalogCompanyGrid->resetFilter();
        $this->assertSortForColumn('Assigned');
        $this->assertSortForColumn('Catalog');
    }

    /**
     * Assert sorting for column by its label.
     *
     * @param string $columnLabel
     * @return void
     */
    private function assertSortForColumn($columnLabel)
    {
        $this->sharedCatalogCompanyGrid->sortByColumn($columnLabel);
        $columnValues = $this->getColumnValues($columnLabel);
        $expectedColumnValues = $columnValues;
        arsort($expectedColumnValues);
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedColumnValues,
            $columnValues,
            'Sorting at shared catalog companies grid is not correct .'
        );
    }

    /**
     * Get column values by its label.
     *
     * @param string $columnLabel
     * @return array
     */
    private function getColumnValues($columnLabel)
    {
        $columnValues = [];
        $ids = $this->sharedCatalogCompanyGrid->getAllIds();

        foreach ($ids as $id) {
            $columnValues[] = $this->sharedCatalogCompanyGrid->getColumnValue($id, $columnLabel);
        }
        return $columnValues;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sorting at shared catalog companies grid is correct.';
    }
}
