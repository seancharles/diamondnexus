<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\Constraint\Product;

use Magento\Catalog\Test\Block\Product\Price;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Class AssertPercentageTierPriceOnProductPage
 */
class AssertPercentageTierPriceInPriceBlockOnProductPage extends AbstractConstraint
{
    /**
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductSimple $initialProduct
     * @param FixtureInterface $product
     * @param array $expectedTierPriceData
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        CatalogProductSimple $initialProduct,
        FixtureInterface $product,
        array $expectedTierPriceData
    ) {
        $urlKey = $product->getUrlKey() ?: $initialProduct->getUrlKey();
        $browser->open($_ENV['app_frontend_url'] . $urlKey . '.html');

        $fixturePercentageValue = $this->findCorrespondingPercentageValue($expectedTierPriceData);
        $priceBlock = $catalogProductView->getViewBlock()->getPriceBlock();

        $this->assertOldPrice($product, $priceBlock);
        $this->assertUpdatedPrice($product, $priceBlock, $fixturePercentageValue);
    }

    /**
     * @param array $tierPrices
     * @return float
     */
    private function findCorrespondingPercentageValue(array $tierPrices)
    {
        $percentageValue = null;
        if ($tierPrices) {
            foreach ($tierPrices as $tierPrice) {
                if (isset($tierPrice['percentage_value'], $tierPrice['price_qty']) && $tierPrice['price_qty'] == 1) {
                    $percentageValue = $tierPrice['percentage_value'];
                    break;
                }
            }
        }
        \PHPUnit\Framework\Assert::assertNotEmpty(
            $percentageValue,
            'There is no percentage_value with qty = 1 in fixture.'
        );
        return $percentageValue;
    }

    /**
     * @param FixtureInterface $product
     * @param Price $priceBlock
     */
    private function assertOldPrice(FixtureInterface $product, Price $priceBlock)
    {
        $fixturePrice = number_format($product->getPrice(), 2, '.', '');
        $currentOldPrice = $priceBlock->getOldPrice();

        \PHPUnit\Framework\Assert::assertEquals(
            $fixturePrice,
            $currentOldPrice,
            'Old price on product page is not correct in price block.'
        );
    }

    /**
     * @param FixtureInterface $product
     * @param Price $priceBlock
     * @param float $fixturePercentageValue
     */
    private function assertUpdatedPrice(FixtureInterface $product, Price $priceBlock, $fixturePercentageValue)
    {
        $fixturePrice = $product->getPrice();
        $expectedPrice = $fixturePrice - ($fixturePrice * $fixturePercentageValue / 100);
        $expectedPrice = number_format($expectedPrice, 2, '.', '');
        $currentPrice = $priceBlock->getPrice();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedPrice,
            $currentPrice,
            'Percentage tier price on product page is not correct in price block.'
        );
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'Percentage tier price is displayed on the product page in price block.';
    }
}
