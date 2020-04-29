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
 * 6. Add some columns.
 * 7. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68568
 */
class CheckColumnsAndFiltersTest extends AbstractSharedCatalogConfigurationTest
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

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
     * Check "Columns" area and filters in the PG.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $products
     * @param string $configData
     * @param string|null $addColumns [optional]
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        array $products,
        $configData,
        $addColumns = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();
        $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run();

        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $this->sharedCatalogConfigure->getStructureGrid()->clickResetButton();
        if ($addColumns !== null) {
            $addColumns = explode(', ', $addColumns);
            $this->sharedCatalogConfigure->getStructureGrid()->checkFieldsInColumnsPanel($addColumns);
            $this->sharedCatalogConfigure->getNavigation()->nextStep();
        }

        return [
            'sharedCatalog' => $sharedCatalog,
        ];
    }

    /**
     * Roll back config settings.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
