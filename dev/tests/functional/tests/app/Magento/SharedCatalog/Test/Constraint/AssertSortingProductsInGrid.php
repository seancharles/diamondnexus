<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that sort order is correct after sorting products by ID.
 */
class AssertSortingProductsInGrid extends AbstractConstraint
{
    /**
     * Assert that sort order is correct after sorting products by ID.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param string $productType
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $catalogProductIndex,
        $productType
    ) {
        $catalogProductIndex->open();
        $catalogProductIndex->getProductGrid()->search(['type' => $productType]);
        // Sorting by Name is necessary if the product grid has already been sorted by ID
        $catalogProductIndex->getProductGrid()->sortByColumn('Name');
        $catalogProductIndex->getProductGrid()->sortByColumn('ID');
        $productIds = $catalogProductIndex->getProductGrid()->getAllIds();
        $sortedProductIds = $this->retrieveSortedProductIds($productIds);

        \PHPUnit\Framework\Assert::assertEquals(
            $productIds,
            $sortedProductIds,
            'Sort order is not correct.'
        );
    }

    /**
     * Retrieve sorted product Ids.
     *
     * @param array $productsIds
     * @return array
     */
    private function retrieveSortedProductIds(array $productsIds)
    {
        sort($productsIds);
        return $productsIds;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sort order is correct.';
    }
}
