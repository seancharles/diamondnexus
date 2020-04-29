<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert products are/aren't present on the search results page.
 */
class AssertProductPresentOnSearchResultsPage extends AbstractConstraint
{
    /**
     * Assert products are/aren't present on the search results page.
     *
     * @param CmsIndex $cmsIndex
     * @param AdvancedResult $searchResults
     * @param array $productsPresentInCatalog
     * @param array $productsAbsentInCatalog
     * @param array $productsDisabled
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        AdvancedResult $searchResults,
        array $productsPresentInCatalog,
        array $productsAbsentInCatalog,
        array $productsDisabled
    ) {
        $cmsIndex->open();
        $this->verifyProductsOnSearchResultsPage($cmsIndex, $searchResults, $productsPresentInCatalog);
        $this->verifyProductsOnSearchResultsPage(
            $cmsIndex,
            $searchResults,
            array_merge($productsAbsentInCatalog, $productsDisabled),
            false
        );
    }

    /**
     * Verify that products are/aren't present on the search results page.
     *
     * @param CmsIndex $cmsIndex
     * @param AdvancedResult $searchResults
     * @param array $products
     * @param bool $isPresent
     * @return void
     */
    private function verifyProductsOnSearchResultsPage(
        CmsIndex $cmsIndex,
        AdvancedResult $searchResults,
        array $products,
        $isPresent = true
    ) {
        $searchBlock = $cmsIndex->getSearchBlock();
        foreach ($products as $product) {
            foreach ([$product->getSku(), $product->getName()] as $query) {
                $searchBlock->search($query);
                $isProductVisible = $searchResults->getListProductBlock()->getProductItem($product)->isVisible();
                while (!$isProductVisible && $searchResults->getBottomToolbar()->nextPage()) {
                    $isProductVisible = $searchResults->getListProductBlock()->getProductItem($product)->isVisible();
                }

                \PHPUnit\Framework\Assert::assertTrue(
                    $isPresent ? $isProductVisible : !$isProductVisible,
                    'Product \'' . $product->getName() . '\' is ' . ($isPresent ? 'absent' : 'present')
                    . ' on the search results page.'
                );
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products are/aren\'t present on the search results page';
    }
}
