<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert empty categories from shared catalog are not present on the storefront.
 */
class AssertEmptyCategoriesNotPresentInCatalog extends AbstractConstraint
{
    /**
     * Assert empty categories from shared catalog are not present on the storefront.
     *
     * @param CmsIndex $cmsIndex
     * @param array $emptyCategoriesAbsentInCatalog
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        array $emptyCategoriesAbsentInCatalog
    ) {
        $cmsIndex->open();
        foreach ($emptyCategoriesAbsentInCatalog as $category) {
            \PHPUnit\Framework\Assert::assertFalse(
                $cmsIndex->getNavigationMenu()->isCategoryVisible($category->getName()),
                'Category \'' . $category->getName() . '\' is present in the top menu.'
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
        return 'Empty categories from shared catalog are not present on the storefront.';
    }
}
