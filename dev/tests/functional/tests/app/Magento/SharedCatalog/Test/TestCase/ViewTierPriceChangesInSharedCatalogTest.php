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
 *
 * Steps:
 * 1. Assign product to the shared catalog.
 * 2. Open product edit page.
 * 3. Open advanced price settings section.
 * 4. Add tier price row.
 * 5. Save product.
 * 6. Open shared catalog.
 * 7. Remove product tier price.
 * 8. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68472
 */
class ViewTierPriceChangesInSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
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
     * Advanced pricing section code.
     *
     * @var string
     */
    private $advancedPricingSection = 'advanced-pricing';

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
     * @param SharedCatalog $sharedCatalog
     * @param array $productsList
     * @param string $qty
     * @param string $price
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        array $productsList,
        $qty,
        $price
    ) {
        $sharedCatalog->persist();
        $products = $this->persistProduct($productsList);
        $product = $products[0];
        $this->openSharedCatalogConfiguration($sharedCatalog);
        $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);
        $this->catalogProductNew->getProductForm()->openSection($this->advancedPricingSection);
        $options = [
            'value' => [
                [
                    'customer_group' => $sharedCatalog->getName(),
                    'price_qty' => $qty,
                    'price' => $price
                ]
            ]
        ];
        $this->catalogProductNew->getTierPrice()->setOptions($options);
        $this->catalogProductNew->getAdvancedPricing()->save();
        $this->catalogProductNew->getFormPageActions()->save();
        $this->openSharedCatalogConfiguration($sharedCatalog);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPricingGrid()->search(['sku' => $product->getSku()]);
        $this->sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();
        $this->sharedCatalogConfigure->getTierPriceModal()->deleteAllTierPrices();
        $this->sharedCatalogConfigure->getTierPriceModal()->save();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');

        return ['sku' => $product->getSku()];
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
}
