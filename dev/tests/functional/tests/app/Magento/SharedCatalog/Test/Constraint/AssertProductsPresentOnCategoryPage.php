<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert products are present on the category page with correct price.
 */
class AssertProductsPresentOnCategoryPage extends AbstractConstraint
{
    /**
     * Assert products are present on the category page with correct price.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $categoryView
     * @param array $productsPresentOnCategoryPage
     * @param float|int $discount
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $categoryView,
        array $productsPresentOnCategoryPage,
        $discount
    ) {
        $cmsIndex->open();
        foreach ($productsPresentOnCategoryPage as $product) {
            $categoryName = $product->hasData('category_ids') ? $product->getCategoryIds()[0] : null;
            $cmsIndex->getNavigationMenu()->selectCategoryByName($categoryName);
            $isProductVisible = $categoryView->getListProductBlock()->getProductItem($product)->isVisible();
            while (!$isProductVisible && $categoryView->getBottomToolbar()->nextPage()) {
                $isProductVisible = $categoryView->getListProductBlock()->getProductItem($product)->isVisible();
            }
            $isPriceCorrect = false;
            if ($isProductVisible) {
                $isPriceCorrect = $categoryView->getListProductBlock()
                        ->getProductItem($product)->getPriceBlock()->getPrice()
                    == number_format($product->getPrice() * (100 - $discount) / 100, 2);
            }
            \PHPUnit\Framework\Assert::assertTrue(
                $isProductVisible && $isPriceCorrect,
                'Product \'' . $product->getName() . '\' is absent on category page or price is incorrect.'
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
        return 'Products are present on the category page with correct price.';
    }
}
