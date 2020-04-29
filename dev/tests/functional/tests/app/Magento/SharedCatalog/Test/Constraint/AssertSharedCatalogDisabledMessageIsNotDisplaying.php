<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that shared catalog feature disabled message is not displaying.
 */
class AssertSharedCatalogDisabledMessageIsNotDisplaying extends AbstractConstraint
{
    /**
     * Check that shared catalog feature disabled message is not displaying.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex)
    {
        $sharedCatalogIndex->open();

        \PHPUnit\Framework\Assert::assertFalse(
            $sharedCatalogIndex->getMessages()->isVisibleMessage('warning'),
            'Shared Catalog feature disabled message is displaying.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared Catalog feature disabled message is not displaying.';
    }
}
