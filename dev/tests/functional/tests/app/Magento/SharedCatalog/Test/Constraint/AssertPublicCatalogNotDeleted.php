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
 * Assert public shared catalog couldn't be deleted.
 */
class AssertPublicCatalogNotDeleted extends AbstractConstraint
{

    /**
     * Assert public shared catalog couldn't be deleted.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex, SharedCatalog $sharedCatalog)
    {
        $sharedCatalogIndex->getMessages()->assertErrorMessage();
        $errorMessage = $sharedCatalogIndex->getMessages()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertTrue(
            false !== strpos($errorMessage, 'cannot be deleted because it is a public catalog.'),
            'Shared Catalog \'' . $sharedCatalog->getName() . '\' not present in pages grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog is present in pages grid.';
    }
}
