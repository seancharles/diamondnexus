<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;

/**
 * Check is error message appear after trying to set already existing shared catalog name.
 */
class AssertSharedCatalogAlreadyExistsMessage extends AbstractConstraint
{
    /**
     * Check that shared catalog is in grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @param string $sharedCatalogName
     * @param string $defaultSharedCatalogName
     * @param string $errorMessagePattern
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate,
        $sharedCatalogName,
        $defaultSharedCatalogName,
        $errorMessagePattern
    ) {
        $sharedCatalogIndex->open();
        $filter = ['name' => $sharedCatalogName];
        $sharedCatalogIndex->getGrid()->search($filter);
        $sharedCatalogIndex->getGrid()->openEdit($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogCreate->getSharedCatalogForm()->setName($defaultSharedCatalogName);
        $sharedCatalogCreate->getFormPageActions()->save();

        \PHPUnit\Framework\Assert::assertEquals(
            sprintf($errorMessagePattern, $defaultSharedCatalogName),
            $sharedCatalogCreate->getMessagesBlock()->getErrorMessage(),
            'Shared catalog error message is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog error message is correct.';
    }
}
