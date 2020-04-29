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
 * Check option in customer group field after typing text in search input on "Product new" page.
 */
class AssertProductNewPageCustomerGroupField extends AbstractConstraint
{
    /**
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param string $customerGroupName
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        $customerGroupName
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $newProductPage->getProductForm()->openSection('advanced-pricing');
        $newProductPage->getCustomerGroup()->openCustomerGroupPrice();
        $newProductPage->getCustomerGroup()->searchGroupByName($customerGroupName);
        \PHPUnit\Framework\Assert::assertEquals(
            $customerGroupName,
            $newProductPage->getCustomerGroup()->getResultFromField(),
            'Customer group field is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group field is correct.';
    }
}
