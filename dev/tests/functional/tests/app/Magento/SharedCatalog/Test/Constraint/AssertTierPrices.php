<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

class AssertTierPrices extends AbstractConstraint
{
    /**
     * Price column title in pricing grid in shared catalog.
     */
    const PRICE_COLUMN = 'Price';

    /**
     * New price column title in pricing grid in shared catalog.
     */
    const NEW_PRICE_COLUMN = 'New Price';

    /**
     * Assert products have correct tier prices
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param array $products
     * @param array $tierPrices
     * @param array $data
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        array $products,
        array $tierPrices,
        array $data
    ) {
        $pricingGrid = $sharedCatalogConfigure->getPricingGrid();
        $tierPriceModal = $sharedCatalogConfigure->getTierPriceModal();
        foreach ($products as $product) {
            $pricingGrid->search(['sku' => $product->getSku()]);
            $prices = $tierPrices[$product->getSku()];
            $productId = $product->getId();
            if ($product instanceof ConfigurableProduct) {
                $childProducts = $product
                    ->getDataFieldConfig('configurable_attributes_data')['source']
                    ->getProducts();
                $product = reset($childProducts);
            }
            if ($product->getPrice()) {
                \PHPUnit\Framework\Assert::assertTrue(
                    strpos(
                        $pricingGrid->getColumnValue($productId, self::PRICE_COLUMN),
                        $product->getPrice()
                    ) !== false,
                    'Wrong product price.'
                );
                \PHPUnit\Framework\Assert::assertTrue(
                    strpos(
                        $pricingGrid->getColumnValue($productId, self::NEW_PRICE_COLUMN),
                        (string)($product->getPrice() - $product->getPrice()*$data['discount']/100)
                    ) !== false,
                    'Wrong product custom price.'
                );
            }
            if (!empty($prices) && $pricingGrid->canConfigurePrice()) {
                $pricingGrid->openTierPriceConfiguration();
                foreach ($prices as $price) {
                    $rowData = $tierPriceModal->getRowDataByQty($price['qty']);
                    \PHPUnit\Framework\Assert::assertEquals(
                        $price['value_type'],
                        $rowData['value_type'],
                        'Wrong value_type for tier price with qty = 1'
                    );
                    \PHPUnit\Framework\Assert::assertEquals(
                        $price['percentage_value'],
                        $rowData['percentage_value'],
                        'Wrong value for tier price with qty = 1'
                    );
                }
                $tierPriceModal->close();
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
        return 'Tier Price modal contains correct prices';
    }
}
