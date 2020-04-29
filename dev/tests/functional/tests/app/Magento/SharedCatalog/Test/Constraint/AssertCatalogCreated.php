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
 * Assert shared catalog shows in grid.
 */
class AssertCatalogCreated extends AbstractConstraint
{
    /**
     * Assert shared catalog shows in grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex, SharedCatalog $sharedCatalog)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $sharedCatalogIndex->getGrid()->isRowVisible(['name' => $sharedCatalog->getName()]),
            'Shared catalog \'' . $sharedCatalog->getName() . '\' isn\'t present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog was created.';
    }
}
