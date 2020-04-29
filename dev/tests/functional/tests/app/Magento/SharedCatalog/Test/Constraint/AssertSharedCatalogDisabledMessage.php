<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that shared catalog feature disabled message is displaying and correct.
 */
class AssertSharedCatalogDisabledMessage extends AbstractConstraint
{
    /**
     * Check that shared catalog feature disabled message is displaying and correct.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param string $sharedCatalogFeatureDisabledMessage
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex, $sharedCatalogFeatureDisabledMessage)
    {
        $sharedCatalogIndex->open();

        \PHPUnit\Framework\Assert::assertEquals(
            $sharedCatalogIndex->getMessages()->getWarningMessage(),
            $sharedCatalogFeatureDisabledMessage,
            'Shared Catalog feature disabled message is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared Catalog feature disabled message is displaying and correct.';
    }
}
