<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Assert "Add to Requisition List" link is visible in customer menu, on product and category pages
 */
class AssertRequisitionListLinkIsVisible extends AbstractConstraint
{
    /**
     * Assert "Add to Requisition List" link is visible in customer menu, on product and category pages
     *
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param CatalogCategoryView $catalogCategoryView
     * @param Category $category
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        CatalogCategoryView $catalogCategoryView,
        Category $category
    ) {
        $cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->checkLinkIsVisibleInCustomerMenu($cmsIndex);
        $this->checkLinkIsVisibleOnProductPage($product, $catalogProductView, $browser);
        $this->checkLinkIsVisibleOnCategoryPage($category, $catalogCategoryView, $browser);
    }

    /**
     * @param CmsIndex $cmsIndex
     */
    public function checkLinkIsVisibleInCustomerMenu(CmsIndex $cmsIndex)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('Requisition Lists'),
            'Requisition list link is not visible.'
        );
    }

    /**
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     */
    public function checkLinkIsVisibleOnProductPage(
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getProductSocialLinksBlock()->isLinkVisible('Add to Requisition List'),
            'Requisition list link is not visible on product page.'
        );
    }

    /**
     * @param Category $category
     * @param CatalogCategoryView $catalogCategoryView
     * @param BrowserInterface $browser
     */
    public function checkLinkIsVisibleOnCategoryPage(
        Category $category,
        CatalogCategoryView $catalogCategoryView,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogCategoryView->getProductActionsBlock()->isLinkVisible('Add to Requisition List'),
            'Requisition list link is not visible on category page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list link is visible';
    }
}
