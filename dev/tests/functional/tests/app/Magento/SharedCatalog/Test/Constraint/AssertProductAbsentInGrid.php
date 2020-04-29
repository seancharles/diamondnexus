<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;

/**
 * Assert deleted products are absent on pricing grid.
 */
class AssertProductAbsentInGrid extends AbstractConstraint
{
    /**
     * Assert deleted products are absent on pricing grid.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog          $sharedCatalog
     * @param array                  $product
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        array $product
    ) {
        $sharedCatalogConfigure->open(['shared_catalog_id' => $sharedCatalog->getId()]);
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $sharedCatalogConfigure->getNavigation()->nextStep();
        
        foreach ((array)$product as $item) {
            \PHPUnit\Framework\Assert::assertFalse(
                $sharedCatalogConfigure->getPricingGrid()->isRowVisible($item),
                'Deleted product is displayed in shared catalog grid.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Deleted product are not visible in shared catalog.';
    }
}
