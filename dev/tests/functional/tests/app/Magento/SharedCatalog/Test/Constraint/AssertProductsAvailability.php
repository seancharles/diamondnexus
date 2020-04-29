<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that products are/aren't available by direct link.
 */
class AssertProductsAvailability extends AbstractConstraint
{
    /**
     * Name of the default category.
     */
    const DEFAULT_CATEGORY_NAME = 'Default Category';

    /**
     * Assert that products are/aren't available by direct link.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $productView
     * @param array $productsAvailability
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $productView,
        array $productsAvailability
    ) {
        foreach ($productsAvailability as $data) {
            $productCategories = $data['product']->getDataFieldConfig('category_ids')['source']->getCategories();
            $browser->open(
                $_ENV['app_frontend_url']
                . (!empty($productCategories) ? $this->getFullUrlKey($productCategories[0]) : '')
                . $data['product']->getUrlKey() . '.html'
            );
            $title = $productView->getTitleBlock()->getTitle();
            \PHPUnit\Framework\Assert::assertEquals(
                $data['available'],
                strpos($title, $data['product']->getName()) !== false,
                sprintf(
                    $data['product']->getName(),
                    'Product \'%s\' %s available by direct link.',
                    $data['available'] ? 'isn\'t' : 'is'
                )
            );
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
            $parentKey = $this->getFullUrlKey($parent);
        }
        return $parentKey . $category->getUrlKey() . '/';
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products availability by direct link is correct.';
    }
}
