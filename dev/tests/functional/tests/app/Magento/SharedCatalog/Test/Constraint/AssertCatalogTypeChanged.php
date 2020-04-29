<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert shared catalog type changed
 */
class AssertCatalogTypeChanged extends AbstractConstraint
{
    const CATALOG_PUBLIC = 'Public';

    /** @var SharedCatalogIndex */
    protected $sharedCatalogIndex;

    /**
     * Assert shared catalog type changed
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalog $sharedCatalog
     * @param string $publicName
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalog $sharedCatalog,
        $publicName
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $grid = $this->sharedCatalogIndex->getGrid();
        $grid->search(['name' => $sharedCatalog->getName()]);
        $publicId = $grid->getFirstItemId();

        \PHPUnit\Framework\Assert::assertTrue(
            $grid->getColumnValue($publicId, 'Type') == self::CATALOG_PUBLIC,
            'Shared catalog type wasn\'t changed to Public.'
        );
        $grid->search(['name' => $publicName]);
        $customId = $grid->getFirstItemId();
        \PHPUnit\Framework\Assert::assertTrue(
            $grid->getColumnValue($customId, 'Type') == 'Custom',
            'Shared catalog type wasn\'t changed to Custom.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog type was successfully changed to Public.';
    }
}
