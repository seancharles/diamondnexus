<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert category exist in categories tree on configure step.
 */
class AssertCategoryCheckedInTree extends AbstractConstraint
{
    /**
     * Assert category exist in category tree.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param CatalogProductSimple $catalogProduct
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        CatalogProductSimple $catalogProduct
    ) {
        $categoryName = $catalogProduct->getCategoryIds()[0];
        \PHPUnit\Framework\Assert::assertTrue(
            $sharedCatalogConfigure->getStructureJstree()->findTreeNode($categoryName)->isVisible(),
            'No category was shown on structure configuration step.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category is displaying in categories tree on configure step.';
    }
}
