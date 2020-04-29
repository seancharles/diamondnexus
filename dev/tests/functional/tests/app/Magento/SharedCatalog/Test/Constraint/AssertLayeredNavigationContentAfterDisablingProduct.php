<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertLayeredNavigationContentAfterDisablingProduct.
 */
class AssertLayeredNavigationContentAfterDisablingProduct extends AbstractLayeredNavigationAssert
{
    /**
     * Catalog product index.
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Expected filter items.
     *
     * @var array
     */
    private $expectedFilterItems = [
        1 => "$0.00 - $99.99 1\nitem",
        "$500.00 and above 1\nitem",
    ];

    /**
     * Assert layered navigation filter items text.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductIndex $catalogProductIndex
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $productToDisable
     * @param string $categoryName
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CatalogProductIndex $catalogProductIndex,
        CmsIndex $cmsIndex,
        FixtureInterface $productToDisable,
        $categoryName
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->disableProduct($productToDisable);
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $catalogCategoryView->getSharedCatalogLayeredNavigationBlock()->expandFilterGroup();
        foreach ($this->expectedFilterItems as $index => $expectedValue) {
            $this->assertFilterItemText(
                $expectedValue,
                $catalogCategoryView->getSharedCatalogLayeredNavigationBlock()->getFilterItemTextByIndex($index)
            );
        }
    }

    /**
     * Disable product.
     *
     * @param FixtureInterface $product
     * @return void
     */
    private function disableProduct(FixtureInterface $product)
    {
        $this->catalogProductIndex->open();
        $filter = ['name' => $product->getName()];
        $this->catalogProductIndex->getProductGrid()->massaction([$filter], ['Change status' => 'Disable']);
    }
}
