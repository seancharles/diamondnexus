<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert new price values in shared catalog pricing grid.
 */
class AssertNewPrice extends AbstractConstraint
{
    /**
     * Assert new price values in shared catalog pricing grid.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param array $products
     * @param array $customPrices
     * @param string $websiteName
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        array $products,
        array $customPrices,
        $websiteName
    ) {
        $sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($websiteName);
        foreach ($products as $key => $product) {
            $sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);
            $rowId = $sharedCatalogConfigure->getPricingGrid()->getFirstItemId();

            \PHPUnit\Framework\Assert::assertEquals(
                $customPrices[$key]['new_price'],
                $sharedCatalogConfigure->getPricingGrid()->getColumnValue($rowId, 'New Price'),
                'New price value for product \'' . $product->getName() . '\'' . ' is incorrect.'
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
        return 'New price values are correct.';
    }
}
