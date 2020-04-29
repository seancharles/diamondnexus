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
 * 10. Go to Products > Shared Catalogs.
 * 11. Filter Shared catalog by name.
 * 12. Select Shared catalog.
 * 13. Select Delete from Mass action menu.
 * 14. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-71457
 */
class ConfigureAndDeleteSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
{
    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject dependencies.
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
     * Add products into the shared catalog and delete the shared catalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $products
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, array $products)
    {
        $sharedCatalog->persist();
        $products = $this->persistProducts($products);
        $skuList = [];
        foreach ($products as $product) {
            $skuList[] = $product->getSku();
        }
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($skuList as $sku) {
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $sku]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->searchAndSelect(['name' => $sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->clickMassDelete();
        $this->sharedCatalogIndex->getModalBlock()->acceptAlert();

        return [
            'products' => $products
        ];
    }

    /**
     * Persist products.
     *
     * @param array $products
     * @return array
     */
    private function persistProducts(array $products)
    {
        $createProductsStep = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );
        return $createProductsStep->run()['products'];
    }
}
