<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Class AssertLayeredNavigationContent.
 */
class AssertLayeredNavigationContent extends AbstractLayeredNavigationAssert
{
    /**
     * Catalog product index.
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Catalog product edit.
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;

    /**
     * Expected filter items.
     *
     * @var array
     */
    private $expectedFilterItems = [
        1 => "$0.00 - $99.99 1\nitem",
        "$100.00 - $199.99 1\nitem",
        "$500.00 and above 1\nitem",
    ];

    /**
     * Css locator for loader.
     *
     * @var string
     */
    protected $loader = '.loading-mask';

    /**
     * Assert layered navigation filter items text.
     *
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $catalogCategoryView->getSharedCatalogLayeredNavigationBlock()->expandFilterGroup();
        foreach ($this->expectedFilterItems as $index => $expectedValue) {
            $this->assertFilterItemText(
                $expectedValue,
                $catalogCategoryView->getSharedCatalogLayeredNavigationBlock()->getFilterItemTextByIndex($index)
            );
        }
    }
}
