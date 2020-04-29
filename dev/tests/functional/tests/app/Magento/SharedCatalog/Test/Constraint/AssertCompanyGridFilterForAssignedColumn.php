<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that filtering by 'assigned' column works at shared catalog company grid.
 */
class AssertCompanyGridFilterForAssignedColumn extends AbstractConstraint
{
    /**
     * @var \Magento\SharedCatalog\Test\Block\Adminhtml\CompanyGrid
     */
    private $sharedCatalogCompanyGrid;

    /**
     * Assert that filtering by 'assigned' column works.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
    ) {
        $this->sharedCatalogCompanyGrid = $sharedCatalogCompany->getCompanyGrid();
        $filter = ['is_current' => 'No'];
        $this->sharedCatalogCompanyGrid->search($filter);
        $columnValues = $this->getColumnValues('Assigned');
        $result = count(array_keys($columnValues, 'No')) == count($columnValues);
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Filtering at shared catalog company grid is not correct.'
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
        return 'Filtering at shared catalog company grid is correct';
    }
}
