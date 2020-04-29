<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;
use Magento\QuickOrder\Test\Block\Items\Item as QuickOrderItemBlock;
use Magento\QuickOrder\Test\Block\Items\Item\Autocomplete as AutocompleteBlock;

/**
 * Test quick order autocomplete
 *
 * Test Flow:
 * 1. Open quick order page.
 * 2. Fill sku
 * 3. Click autocomplete
 * 4. Fill invalid sku
 * 5. Fill sku
 * 6. Click autocomplete
 * 4. Perform asserts.
 *
 * @group QuickOrder
 * @ZephyrId MAGETWO-68272
 */
class QuickOrderAutocompleteTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var QuickOrderPage
     */
    private $quickOrderPage;

    /**
     * @var string
     */
    protected $configData;

    /**
     * Perform needed injections.
     *
     * @param QuickOrderPage $quickOrderPage
     * @return void
     */
    public function __inject(QuickOrderPage $quickOrderPage)
    {
        $this->quickOrderPage = $quickOrderPage;
    }

    /**
     * Test autocomplete.
     *
     * @param array $productsList
     * @param string $configData [optional]
     * @return array
     */
    public function test(array $productsList, $configData = null)
    {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->createProducts($productsList);

        $this->quickOrderPage->open();

        $this->quickOrderPage->getItems()->waitForBlockInit();

        $product = current($products);
        $validItemBlock = $this->quickOrderPage->getItems()->getLastItemBlock();
        $this->getAutocompleteBlock($validItemBlock, $product->getSku())
            ->selectSuggestion($product->getSku());

        $invalidItemBlock = $this->quickOrderPage->getItems()->getLastItemBlock();
        $invalidItemBlock->setInvalidSku('undefined');

        $product = next($products);
        $this->getAutocompleteBlock($validItemBlock, $product->getSku())
            ->selectSuggestion($product->getSku());
        sleep(3);

        return [
            'validProducts' => [$product],
            'invalidProductSkus' => ['undefined']
        ];
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    protected function createProducts(array $products)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Get autocomplete block when it's visible for specific item block and SKU.
     *
     * @param QuickOrderItemBlock $itemBlock
     * @param string $sku
     * @return AutocompleteBlock
     * @throws \Exception
     */
    private function getAutocompleteBlock(QuickOrderItemBlock $itemBlock, $sku)
    {
        $autocompleteBlock = $itemBlock->getAutocompleteBlock();
        $counter = 0;

        do {
            if ($counter) {
                $itemBlock->setSku('invalid');
                sleep(5);
            }

            $counter += 1;
            $itemBlock->setSku($sku);
            sleep(5);
        } while ($counter < 5 && !$autocompleteBlock->isVisible());

        if (!$autocompleteBlock->isVisible()) {
            throw new \Exception(sprintf('Autocomplete block for SKU "%s" did not show up!', $sku));
        }

        return $autocompleteBlock;
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
