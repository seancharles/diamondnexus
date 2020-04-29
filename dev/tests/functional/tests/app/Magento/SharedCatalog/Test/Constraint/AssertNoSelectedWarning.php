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
 * Assert warning when no catalog selected on delete.
 */
class AssertNoSelectedWarning extends AbstractConstraint
{

    /**
     * Assert warning when no catalog selected on delete.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalog $sharedCatalog
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex, SharedCatalog $sharedCatalog)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            'You haven\'t selected any items!',
            $sharedCatalogIndex->getModalBlock()->getText(),
            'Modal message should be created.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Modal message is showing.';
    }
}
