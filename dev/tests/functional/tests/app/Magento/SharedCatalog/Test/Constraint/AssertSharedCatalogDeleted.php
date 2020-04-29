<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert shared catalog was deleted.
 */
class AssertSharedCatalogDeleted extends AbstractConstraint
{

    /**
     * Assert shared catalog is absent in grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex, SharedCatalog $sharedCatalog)
    {
        $filter = ['name' => $sharedCatalog->getName()];
        \PHPUnit\Framework\Assert::assertFalse(
            $sharedCatalogIndex->getGrid()->isRowVisible($filter),
            'Shared Catalog \'' . $sharedCatalog->getName() . '\' is present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog is not present in pages grid.';
    }
}
