<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Mtf\Util\Command\Cli\Queue;

/**
 * Class ConfigureSharedCatalogStep.
 * Configure shared catalog step.
 */
class ConfigureSharedCatalogStep implements TestStepInterface
{
    /**
     * TierPriceInterface::PRICE_TYPE_FIXED
     */
    const PRICE_TYPE_FIXED = 'fixed';

    /**
     * TierPriceInterface::PRICE_TYPE_DISCOUNT
     */
    const PRICE_TYPE_DISCOUNT = 'discount';

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var SharedCatalogConfigure $sharedCatalogConfigure
     */
    protected $sharedCatalogConfigure;

    /**
     * Shared catalog.
     *
     * @var SharedCatalog
     */
    protected $sharedCatalog;

    /**
     * Array of products which should be assigned to shared catalog.
     *
     * @var CatalogProductSimple[]
     */
    protected $products;

    /**
     * Array of products which should be unassigned from shared catalog.
     *
     * @var CatalogProductSimple[]
     */
    protected $unassignProducts;

    /**
     * @var array
     */
    protected $data;

    /**
     * @constructor
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param Queue $queue
     * @param array $products
     * @param array $unassignProducts
     * @param array $data [optional]
     */
    public function __construct(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        Queue $queue,
        array $products = [],
        array $unassignProducts = [],
        array $data = []
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
        $this->sharedCatalog = $sharedCatalog;
        $this->queue = $queue;
        $this->products = $products;
        $this->unassignProducts = $unassignProducts;
        $this->data = $data;
    }

    /**
     * Configure shared catalog.
     *
     * @return void
     */
    public function run()
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $this->sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->openConfigure($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        foreach ($this->products as $product) {
            $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $product->getSku()]);
        }
        foreach ($this->unassignProducts as $product) {
            $this->sharedCatalogConfigure->getStructureGrid()->uncheckSwitcherItem(['sku' => $product->getSku()]);
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure
            ->getPricingGrid()
            ->resetFilter();
        if (!empty($this->data['discount'])) {
            if ($this->data['type'] == self::PRICE_TYPE_DISCOUNT) {
                $this->sharedCatalogConfigure
                    ->getPricingGrid()
                    ->applyDiscount();
            } elseif ($this->data['type'] == self::PRICE_TYPE_FIXED) {
                $this->sharedCatalogConfigure
                    ->getPricingGrid()
                    ->adjustFixedPrice();
            }
            $this->sharedCatalogConfigure->getDiscount()->setAlertText($this->data['discount']);
            $this->sharedCatalogConfigure->getDiscount()->acceptAlert();
            $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();
        }
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();

        // Run queues to set tier prices and catalog permissions after shared catalog assignment operations
        $this->queue->run('sharedCatalogUpdateCategoryPermissions');
        $this->queue->run('sharedCatalogUpdatePrice');
    }
}
