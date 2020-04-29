<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Validate tier price currency symbols in shared catalog tier pricing grid in different scopes.
 */
class AssertTierPricesCurrencyInWebsite extends AbstractConstraint
{
    /**
     * All websites id.
     *
     * @var int
     */
    private $allWebsitesId = 0;

    /**
     * Default qty.
     *
     * @var int
     */
    private $defaultQty = 1;

    /**
     * All websites filter.
     *
     * @var string
     */
    private $allWebsiteFilter = 'All Websites';

    /**
     * Validate tier price currency symbols in shared catalog.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param CatalogProductSimple $product
     * @param array $websites
     * @param array $expectedCurrencies
     * @param array $allWebsitesCurrency
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        CatalogProductSimple $product,
        array $websites,
        array $expectedCurrencies,
        array $allWebsitesCurrency
    ) {
        $sharedCatalogConfigure->open(['shared_catalog_id' => $sharedCatalog->getId()]);
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $sharedCatalogConfigure->getNavigation()->nextStep();
        $sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);
        $sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();
        \PHPUnit\Framework\Assert::assertArraySubset(
            [$this->allWebsitesId => $allWebsitesCurrency['price']],
            $sharedCatalogConfigure->getTierPriceModal()->getPriceCurrencySymbol($this->defaultQty),
            'Tier price currency symbol in ' . $this->allWebsiteFilter . ' scope is incorrect.'
        );
        foreach ($websites as $key => $website) {
            $sharedCatalogConfigure->getTierPriceModal()->switchScope($website->getName());
            if (isset($expectedCurrencies[$key]['custom_price'])) {
                \PHPUnit\Framework\Assert::assertArraySubset(
                    [$website->getWebsiteId() => $expectedCurrencies[$key]['custom_price']],
                    $sharedCatalogConfigure->getTierPriceModal()->getPriceCurrencySymbol($this->defaultQty),
                    'Tier price currency symbol in ' . $website->getName() . ' scope is incorrect.'
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
        return 'Tier price currency symbols in all scopes are correct.';
    }
}
