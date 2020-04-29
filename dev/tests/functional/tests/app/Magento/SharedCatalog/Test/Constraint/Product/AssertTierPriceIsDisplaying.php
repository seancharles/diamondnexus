<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert tier price is displaying in product advanced pricing popup.
 */
class AssertTierPriceIsDisplaying extends AbstractConstraint
{
    /**
     * Advanced pricing section code.
     *
     * @var string
     */
    private $advancedPricingSection = 'advanced-pricing';

    /**
     * Assert tier price is displaying in product advanced pricing popup.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @param string $sku
     * @param string $sharedCatalogName
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductNew $catalogProductNew,
        $sku,
        $sharedCatalogName
    ) {
        $catalogProductIndex->open();
        $catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $sku]);
        $catalogProductNew->getProductForm()->openSection($this->advancedPricingSection);

        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductNew->getTierPrice()->isTierPriceOptionPresent($sharedCatalogName),
            'Tier price options is not present in advanced price options.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Tier price option is present in advanced price options.';
    }
}
