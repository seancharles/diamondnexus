<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert product categories from shared catalog are present on the storefront.
 */
class AssertProductCategoriesPresentInCatalog extends AbstractConstraint
{
    /**
     * Assert product categories from shared catalog are present on the storefront.
     *
     * @param CmsIndex $cmsIndex
     * @param array $productsPresentOnCategoryPage
     * @param array $productsAbsentOnCategoryPage
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        array $productsPresentOnCategoryPage,
        array $productsAbsentOnCategoryPage
    ) {
        $cmsIndex->open();
        foreach ($productsPresentOnCategoryPage as $product) {
            $categoryName = $product->hasData('category_ids') ? $product->getCategoryIds()[0] : 'No Category';
            \PHPUnit\Framework\Assert::assertTrue(
                $cmsIndex->getNavigationMenu()->isCategoryPresentInMenu($categoryName),
                'Category \'' . $categoryName . '\' is not present in the top menu.'
            );
        }

        foreach ($productsAbsentOnCategoryPage as $product) {
            $categoryName = $product->hasData('category_ids') ? $product->getCategoryIds()[0] : 'No Category';
            \PHPUnit\Framework\Assert::assertFalse(
                $cmsIndex->getNavigationMenu()->isCategoryPresentInMenu($categoryName),
                'Category \'' . $categoryName . '\' is present in the top menu.'
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
        return 'Product categories from shared catalog are present on the storefront.';
    }
}
