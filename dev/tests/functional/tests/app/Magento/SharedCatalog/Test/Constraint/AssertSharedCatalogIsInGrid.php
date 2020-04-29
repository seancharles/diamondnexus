<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check is shared catalog in grid.
 */
class AssertSharedCatalogIsInGrid extends AbstractConstraint
{
    /**
     * Check that shared catalog is in grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param string $sharedCatalogName
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        $sharedCatalogName
    ) {
        $sharedCatalogIndex->open();
        $filter = ['name' => $sharedCatalogName];

        \PHPUnit\Framework\Assert::assertTrue(
            $sharedCatalogIndex->getGrid()->isRowVisible($filter),
            'Shared catalog with name \'' . $sharedCatalogName . '\' is absent in a shared catalogs grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog is in grid.';
    }
}
