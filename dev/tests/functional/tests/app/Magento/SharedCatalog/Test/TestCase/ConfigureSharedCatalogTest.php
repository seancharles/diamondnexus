<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

/**
 * Preconditions:
 * 1. Create shared catalog.
 * 2. Create product with category.
 *
 * Steps:
 * 1. Open Admin.
 * 2. Go to Products > Shared catalogs.
 * 3. Filter by created catalog name.
 * 4. Select Configure in action menu.
 * 5. Open configuration wizard.
 * 6. Expand Tree.
 * 7. Filter products by created category.
 * 8. Select product by clicking on switcher.
 * 9. Go to the Next step.
 * 10. Select product.
 * 11. Select Set Discount from Mass actions.
 * 12. Apply discount.
 * 13. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68471, MAGETWO-68479
 */
class ConfigureSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        TestStepFactory $stepFactory
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Configure SharedCatalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $products
     * @param array $data
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        array $products,
        array $data = []
    ) {
        $sharedCatalog->persist();
        $products = $this->persistProduct($products, $data);
        $skuList = [];
        $tierPrices = [];
        foreach ($products as $product) {
            if ($product instanceof ConfigurableProduct) {
                $childSku = '';
                foreach ($product
                             ->getDataFieldConfig('configurable_attributes_data')['source']
                             ->getProducts() as $childProduct) {
                    $childSku = $childProduct->getSku();
                    break;
                }
                $skuList[] = $childSku;
                $tierPrices[$childSku][] = [
                    'qty' => 1,
                    'value_type' => 'percent',
                    'percentage_value' => $data['discount']
                ];
            }
            $skuList[] = $product->getSku();
            $tierPrices[$product->getSku()][] = [
                'qty' => 1,
                'value_type' => 'percent',
                'percentage_value' => $data['discount']
            ];
        }
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($skuList as $sku) {
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $sku]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure
            ->getPricingGrid()
            ->applyDiscount();
        $this->sharedCatalogConfigure->getDiscount()->setAlertText($data['discount']);
        $this->sharedCatalogConfigure->getDiscount()->acceptAlert();

        return [
            'sharedCatalogConfigure' => $this->sharedCatalogConfigure,
            'products' => $products,
            'tierPrices' => $tierPrices,
            'data' => $data
        ];
    }

    /**
     * @param array $products
     * @return array
     */
    public function persistProduct($products, $data)
    {
        $createProductsStep = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products, 'data' => $data]
        );

        return $createProductsStep->run()['products'];
    }
}
