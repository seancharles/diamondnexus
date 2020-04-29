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
 * 13. Click generate and save catalog
 * 14. Open configured catalog
 * 15. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68027, @ZephyrId MAGETWO-68028
 */
class EditSharedCatalogTest extends AbstractSharedCatalogConfigurationTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Enable category permissions and run consumer for queue
     *
     * @return void
     */
    protected function setupConfig()
    {
        $this->objectManager->getInstance()->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'category_permissions_on']
        )->run();
    }

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogConfigure = $sharedCatalogConfigure;
    }

    /**
     * Configure SharedCatalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param CatalogProductSimple $catalogProduct
     * @param array $data
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        CatalogProductSimple $catalogProduct,
        array $data = []
    ) {
        $this->setupConfig();
        $sharedCatalog->persist();
        $catalogProduct->persist();
        $categoryName = $catalogProduct->getData('category_ids')[0];
        $this->sharedCatalogIndex->open();
        $this->openConfiguration($sharedCatalog->getName());
        $this->sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $this->sharedCatalogConfigure
            ->getStructureJstree()
            ->setTreeType('structure')
            ->expandAll()
            ->findTreeNode($categoryName)
            ->click();
        $this->sharedCatalogConfigure->getStructureGrid()->checkSwitcherItem(['sku' => $catalogProduct->getSku()]);
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPricingGrid()->resetFilter();
        $this->sharedCatalogConfigure
            ->getPricingJstree()
            ->setTreeType('pricing')
            ->findTreeNode($categoryName)
            ->click();
        $this->sharedCatalogConfigure
            ->getPricingGrid()
            ->applyDiscount();
        $this->sharedCatalogConfigure->getDiscount()->setAlertText($data['discount']);
        $this->sharedCatalogConfigure->getDiscount()->acceptAlert();
        $this->sharedCatalogConfigure->getPricingGrid()->waitForLoader();
        $this->sharedCatalogConfigure->getNavigation()->nextStep();
        $this->sharedCatalogConfigure->getPageActionBlock()->save();
        $this->objectManager->getInstance()
            ->create(\Magento\Mtf\Util\Command\Cli\Queue::class)
            ->run('sharedCatalogUpdatePrice');
        $this->openConfiguration($sharedCatalog->getName());

        return [
            'sharedCatalogConfigure' => $this->sharedCatalogConfigure,
            'products' => [$catalogProduct],
            'data' => $data
        ];
    }

    /**
     * Disable Category permissions
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->getInstance()->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'category_permissions_on_rollback']
        )->run();
    }
}
