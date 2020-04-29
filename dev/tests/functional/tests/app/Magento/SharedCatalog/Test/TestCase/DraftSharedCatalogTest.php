<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
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
 * 6. Select products by clicking on switcher.
 * 7. Go to the Next step.
 * 8. Set fixed price for products before mass action.
 * 9. Select Set Discount from Mass actions.
 * 10. Apply discount.
 * 11. Set fixed price for products before return.
 * 12. Return to prev step.
 * 13. Select products by clicking on switcher.
 * 14. Deselect product by clicking on switcher.
 * 15. Go to the Next step.
 * 16. Set fixed price for products before change category.
 * 17. Select child category by clicking on tree.
 * 18. Select root category by clicking on tree.
 * 19. Set fixed price for products before configure.
 * 20. Click Configure link.
 * 21. Click Done.
 * 22. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68573
 */
class DraftSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
{
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
     * Configure SharedCatalog without save.
     *
     * @param SharedCatalog $sharedCatalog
     * @param string $catalogProduct
     * @param int $catalogProductsCount
     * @param array $data
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        $catalogProduct,
        $catalogProductsCount,
        array $data
    ) {
        $catalogProducts = $this->persistProduct($catalogProduct, $catalogProductsCount, $data);
        $sharedCatalog->persist();
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($data['firstApply'] as $productId) {
            $product = $catalogProducts[$productId];
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();

        $pricesMassAction = array_intersect_key($data['prices'], array_flip($data['beforeMassAction']));
        $this->applyPrices($catalogProducts, $pricesMassAction);

        $uncheckProducts = array_diff($data['firstApply'], $data['discount']['products']);
        foreach ($uncheckProducts as $productId) {
            $product = $catalogProducts[$productId];
            $this->sharedCatalogConfigure->getPricingGrid()
                ->selectItem(['sku' => $product->getSku()]);
        }
        $this->sharedCatalogConfigure->getPricingGrid()->applyDiscount();
        $this->sharedCatalogConfigure->getDiscount()->setAlertText($data['discount']['price']);
        $this->sharedCatalogConfigure->getDiscount()->acceptAlert();
        $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();

        $pricesBack = array_intersect_key($data['prices'], array_flip($data['beforeBack']));
        $this->applyPrices($catalogProducts, $pricesBack);
        $this->sharedCatalogConfigure->getNavigation()->prevStep();

        foreach ($data['secondApply'] as $productId) {
            $product = $catalogProducts[$productId];
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        }
        foreach ($data['unsetProduct'] as $productId) {
            $this->sharedCatalogConfigure->getStructureGrid()
                ->uncheckSwitcherItem(['sku' => $catalogProducts[$productId]->getSku()]);
            unset($catalogProducts[$productId]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();

        $pricesCategory = array_intersect_key($data['prices'], array_flip($data['beforeCategorySwitch']));
        $this->applyPrices($catalogProducts, $pricesCategory);
        $this->sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();
        $this->sharedCatalogConfigure->getTierPriceModal()->close();

        $pricingTree = $this->sharedCatalogConfigure->getPricingJstree()->setTreeType('pricing');
        $pricingTree->selectNode($catalogProducts[$data['secondApply'][0]]->getCategoryIds()[0]);
        $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();
        $pricingTree->selectRootNode();

        $pricesConfig = array_intersect_key($data['prices'], array_flip($data['beforeConfigure']));
        $this->applyPrices($catalogProducts, $pricesConfig);
        $this->sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();
        $this->sharedCatalogConfigure->getTierPriceModal()->save();
        $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();

        $customPrices = $this->collectCustomPrices($data);

        return [
            'products' => $catalogProducts,
            'customPrices' => $customPrices
        ];
    }

    /**
     * Create custom price array from $data for assert.
     *
     * @param array $data
     * @return array
     */
    private function collectCustomPrices(array $data)
    {
        $customPrices = [];
        foreach ($data['prices'] as $price) {
            $customPrices[$price['id']] = ['new_price' => sprintf('$%1s', number_format($price['price'], 2))];
        }
        return $customPrices;
    }

    /**
     * Apply prices from $prices array for $products.
     *
     * @param CatalogProductSimple[] $products
     * @param array $prices
     * @return void
     */
    private function applyPrices(array $products, array $prices)
    {
        foreach ($prices as $price) {
            $this->sharedCatalogConfigure->getPricingGrid()->setCustomPriceByFilter(
                ['sku' => $products[$price['id']]->getSku()],
                $price['price']
            );
        }
    }

    /**
     * Run product create step.
     *
     * @param string $product
     * @param string $catalogProductsCount
     * @param array $data
     * @return CatalogProductSimple[]
     */
    private function persistProduct($product, $catalogProductsCount, array $data)
    {
        $products = array_fill(0, $catalogProductsCount, $product);
        $createProductsStep = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products, 'data' => $data]
        );

        return $createProductsStep->run()['products'];
    }
}
