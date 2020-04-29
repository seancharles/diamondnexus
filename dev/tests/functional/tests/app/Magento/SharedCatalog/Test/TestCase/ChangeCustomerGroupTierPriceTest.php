<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Create shared catalog.
 * 2. Create product.
 * 3. Assign product to the shared catalog.
 * 4. Add tier price for the shared catalog.
 *
 * Steps:
 * 1. Open product edit page.
 * 2. Open advanced pricing popup.
 * 3. Change tier price customer group.
 * 4. Save product.
 * 5. Open shared catalog.
 * 6. Add tier price for the product.
 * 7. Save shared catalog.
 * 8. Open shared Catalog.
 * 9. Unassign product from the shared catalog.
 * 10. Save shared catalog.
 * 11. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68473
 */
class ChangeCustomerGroupTierPriceTest extends AbstractSharedCatalogConfigurationTest
{
    /**
     * @var \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * @var \Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew
     */
    private $catalogProductNew;

    /**
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * @var \Magento\SharedCatalog\Test\Fixture\SharedCatalog
     */
    private $sharedCatalog;

    /**
     * @var \Magento\Catalog\Test\Fixture\CatalogProductSimple
     */
    private $product;

    /**
     * @var array
     */
    private $tierPricesData;

    /**
     * Advanced pricing section code.
     *
     * @var string
     */
    private $advancedPricingSection = 'advanced-pricing';

    /**
     * Shared catalog section code.
     *
     * @var string
     */
    private $sharedCatalogSection = 'shared_catalog';

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductNew $catalogProductNew
     * @param TestStepFactory $stepFactory
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductNew $catalogProductNew,
        TestStepFactory $stepFactory
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductNew = $catalogProductNew;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Configure SharedCatalog.
     *
     * @param SharedCatalog $emptySharedCatalog
     * @param SharedCatalog $sharedCatalog
     * @param array $productsList
     * @param array $tierPricesData
     * @param array $steps [optional]
     * @return array
     */
    public function test(
        SharedCatalog $emptySharedCatalog,
        SharedCatalog $sharedCatalog,
        array $productsList,
        array $tierPricesData,
        array $steps = []
    ) {
        $this->tierPricesData = $tierPricesData;
        $sharedCatalog->persist();
        $this->sharedCatalog = $sharedCatalog;
        $emptySharedCatalog->persist();
        $products = $this->persistProduct($productsList);
        $this->product = $products[0];
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $this->product->getSku()]);
        $this->catalogProductNew->getProductForm()->openSection($this->sharedCatalogSection);
        $this->catalogProductNew->getProductInSharedCatalogs()->setSharedCatalogsValue([$sharedCatalog->getName()]);
        $this->catalogProductNew->getProductForm()->openSection($this->advancedPricingSection);
        $options = [
            'value' => [
                0 => [
                    'customer_group' => $this->sharedCatalog->getName(),
                    'price_qty' => $this->tierPricesData[0]['qty'],
                    'price' => $this->tierPricesData[0]['price']
                ]
            ]
        ];
        $this->addTierPrice($options);
        $this->catalogProductNew->getFormPageActions()->save();
        $this->catalogProductNew->getProductForm()->openSection($this->advancedPricingSection);
        $this->catalogProductNew->getTierPrice()->removeAllTierPriceOptions();
        $options = [
            'value' => [
                0 => [
                    'customer_group' => $emptySharedCatalog->getName(),
                    'price_qty' => $this->tierPricesData[0]['qty'],
                    'price' => $this->tierPricesData[0]['price']
                ]
            ]
        ];
        $this->addTierPrice($options);
        $this->catalogProductNew->getFormPageActions()->save();

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $this->$classMethod();
        }
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');

        return [
            'sku' => $this->product->getSku(),
            'sharedCatalogName' => $this->sharedCatalog->getName()
        ];
    }

    /**
     * Add tier price in shared catalog.
     *
     * @return void
     */
    protected function addTierPriceInSharedCatalog()
    {
        $this->openSharedCatalogConfiguration($this->sharedCatalog);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPricingGrid()->search(['sku' => $this->product->getSku()]);
        $this->sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();
        $this->sharedCatalogConfigure->getTierPriceModal()->addTierPrices($this->tierPricesData);
        $this->sharedCatalogConfigure->getTierPriceModal()->save();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
    }

    /**
     * Unassign product from the shared catalog.
     *
     * @return void
     */
    protected function unassignProductFromSharedCatalog()
    {
        $this->openSharedCatalogConfiguration($this->sharedCatalog);
        $this->sharedCatalogConfigure->getStructureGrid()->uncheckSwitcherItem(['sku' => $this->product->getSku()]);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
    }

    /**
     * Open shared catalog configuration page.
     *
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    private function openSharedCatalogConfiguration(SharedCatalog $sharedCatalog)
    {
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
    }

    /**
     * Persist product.
     *
     * @param array $products
     * @return array
     */
    private function persistProduct(array $products)
    {
        $createProductsStep = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Add tier price.
     *
     * @param array $options
     * @return void
     */
    private function addTierPrice(array $options)
    {
        $this->catalogProductNew->getTierPrice()->setOptions($options);
        $this->catalogProductNew->getAdvancedPricing()->save();
    }
}
