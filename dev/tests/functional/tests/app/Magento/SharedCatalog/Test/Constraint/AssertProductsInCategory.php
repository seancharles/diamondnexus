<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that products are/aren't displayed on the category page.
 */
class AssertProductsInCategory extends AbstractConstraint
{
    /**
     * Name of the default category.
     */
    const DEFAULT_CATEGORY_NAME = 'Default Category';

    /**
     * Assert that products are/aren't displayed on the category page.
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $categoryView
     * @param array $categoryProducts
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $categoryView,
        array $categoryProducts
    ) {
        foreach ($categoryProducts as $data) {
            $browser->open($_ENV['app_frontend_url'] . $this->getFullUrlKey($data['category']) . '.html');
            foreach ($data['products'] as $product) {
                $isProductVisible = $categoryView->getListProductBlock()->getProductItem($product)->isVisible();
                while (!$isProductVisible && $categoryView->getBottomToolbar()->nextPage()) {
                    $isProductVisible = $categoryView->getListProductBlock()->getProductItem($product)->isVisible();
                }
                \PHPUnit\Framework\Assert::assertEquals(
                    $data['visible'],
                    $isProductVisible,
                    sprintf(
                        'Product \'%s\' is %s on the category page.',
                        $product->getName(),
                        $data['visible'] ? 'absent' : 'present'
                    )
                );
            }
        }
    }

    /**
     * Prepare full category url key with parent categories.
     *
     * @param Category $category
     * @return string
     */
    private function getFullUrlKey(Category $category)
    {
        $parentKey = '';
        $parent = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
        if ($parent && $parent->getName() != self::DEFAULT_CATEGORY_NAME) {
            $parentKey = $this->getFullUrlKey($parent) . '/';
        }
        return $parentKey . $category->getUrlKey();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products visibility on the category page is correct.';
    }
}
