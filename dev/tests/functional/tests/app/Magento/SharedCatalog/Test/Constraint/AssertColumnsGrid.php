<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert list of columns is correct in "Shared Catalog: Products in Catalog" grid.
 */
class AssertColumnsGrid extends AbstractConstraint
{
    /**
     * Assert list of columns is correct in "Shared Catalog: Products in Catalog" grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param string $expectedColumns
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        $expectedColumns
    ) {
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $expectedColumns = explode(', ', $expectedColumns);
        $filters = $sharedCatalogConfigure->getStructureGrid()->getColumnsGrid();
        $diff = array_diff($expectedColumns, $filters);
        \PHPUnit\Framework\Assert::assertTrue(
            empty($diff),
            'List of columns in "Shared Catalog: Products in Catalog" grid is incorrect: ' . implode(',', $diff)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'List of columns in "Shared Catalog: Products in Catalog" grid is correct.';
    }
}
