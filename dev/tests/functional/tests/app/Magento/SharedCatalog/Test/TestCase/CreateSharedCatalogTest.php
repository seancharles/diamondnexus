<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Tax\Test\Fixture\TaxClass;

/**
 * Preconditions:
 * 1. Create store view.
 *
 * Steps:
 * 1. Open Admin.
 * 2. Go to Products > Shared Catalogs.
 * 3. Click "Add Shared Catalog" button.
 * 4. Fill data according to dataset.
 * 5. Set Customer Tax Class.
 * 6. Click "Save" button.
 * 7. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-67980, @ZephyrId MAGETWO-68489, @ZephyrId MAGETWO-68505
 */
class CreateSharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate $sharedCatalogCreate
     */
    private $sharedCatalogCreate;

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCreate = $sharedCatalogCreate;
    }

    /**
     * Create SharedCatalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param TaxClass $customerTaxClass
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, TaxClass $customerTaxClass)
    {
        $customerTaxClass->persist();
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGridPageActionBlock()->addNew();
        /** @var \Magento\SharedCatalog\Test\Block\Adminhtml\Form\Edit $form */
        $form = $this->sharedCatalogCreate->getSharedCatalogForm();
        $form->waitForElementVisible($form->getTabSelector());
        $form->fill($sharedCatalog);
        $form->setCustomerTaxClass($customerTaxClass->getClassName());
        $this->sharedCatalogCreate->getFormPageActions()->save();

        return [
            'sharedCatalog' => $sharedCatalog,
            'customerTaxClass' => $customerTaxClass->getClassName()
        ];
    }
}
