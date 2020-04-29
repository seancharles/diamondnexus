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
 * Validate price currency symbols in shared catalog pricing grid in different scopes.
 */
class AssertProductPricesPerWebsite extends AbstractConstraint
{
    /**
     * All websites filter.
     *
     * @var string
     */
    private $allWebsiteFilter = 'All Websites';

    /**
     * Validate price currency symbols in shared catalog.
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
        $this->validateCurrencySymbols(
            $sharedCatalogConfigure,
            $product,
            $allWebsitesCurrency,
            $this->allWebsiteFilter
        );
        foreach ($websites as $key => $website) {
            $sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($website->getName());
            $this->validateCurrencySymbols(
                $sharedCatalogConfigure,
                $product,
                $expectedCurrencies[$key],
                $website->getName()
            );
        }
        $sharedCatalogConfigure->getPricingGrid()->filterProductsByWebsite($this->allWebsiteFilter);
    }

    /**
     * Validate price currency symbols in shared catalog pricing grid.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param CatalogProductSimple $product
     * @param array $currencySymbol
     * @param string $websiteName
     * @return void
     */
    private function validateCurrencySymbols(
        SharedCatalogConfigure $sharedCatalogConfigure,
        CatalogProductSimple $product,
        array $currencySymbol,
        $websiteName
    ) {
        $symbolTitles = [
            $currencySymbol['price'] => 'Price',
            $currencySymbol['new_price'] => 'New Price',
        ];

        if (isset($currencySymbol['custom_price'])) {
            $symbolTitles[$currencySymbol['custom_price']] = 'Custom Price';
        }

        foreach ($symbolTitles as $symbol => $title) {
            $this->validateSymbol($sharedCatalogConfigure, $product, $symbol, $title, $websiteName);
        }
    }

    /**
     * Validate currency in column.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param CatalogProductSimple $product
     * @param string $currencySymbol
     * @param string $columnTitle
     * @param string $websiteName
     * @return void
     */
    private function validateSymbol(
        SharedCatalogConfigure $sharedCatalogConfigure,
        CatalogProductSimple $product,
        $currencySymbol,
        $columnTitle,
        $websiteName
    ) {
        \PHPUnit\Framework\Assert::assertTrue(
            mb_strpos(
                $sharedCatalogConfigure->getPricingGrid()->getColumnValue($product->getId(), $columnTitle),
                $currencySymbol
            ) !== false,
            '"' . $columnTitle . '"' . ' currency in ' . $websiteName . ' scope is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Currency symbols in all columns are correct.';
    }
}
