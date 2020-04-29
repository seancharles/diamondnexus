<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert right product presents on pricing grid.
 */
class AssertTierPriceConfigureLink extends AbstractConstraint
{
    /**
     * Assert right product presents on pricing grid.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param array $products
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        array $products
    ) {
        foreach ($products as $product) {
            $sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);
            \PHPUnit\Framework\Assert::assertEquals(
                $product->getIsTierPriceAllowed(),
                $sharedCatalogConfigure->getPricingGrid()->canConfigurePrice(),
                'Tier price configuration not allowed for product ' . $product->getSku()
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
        return 'Product is showing right after selecting.';
    }
}
