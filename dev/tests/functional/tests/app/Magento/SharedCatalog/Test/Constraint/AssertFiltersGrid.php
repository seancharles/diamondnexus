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
 * Assert list of filters is correct in "Shared Catalog: Products in Catalog" the filter panel.
 */
class AssertFiltersGrid extends AbstractConstraint
{
    /**
     * Assert list of filters is correct in "Shared Catalog: Products in Catalog" filter panel.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param string $expectedFilters
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        $expectedFilters
    ) {
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $expectedFilters = explode(', ', $expectedFilters);
        $filters = $sharedCatalogConfigure->getStructureGrid()->getFiltersTitle();
        $diff = array_diff($expectedFilters, $filters);

        \PHPUnit\Framework\Assert::assertTrue(
            empty($diff),
            'List of filters in "Shared Catalog: Products in Catalog" filter panel is incorrect: ' . implode(',', $diff)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'List of filters in "Shared Catalog: Products in Catalog" filter panel is correct.';
    }
}
