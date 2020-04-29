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
use Magento\SharedCatalog\Test\Block\Adminhtml\Form\Edit;
use Magento\SharedCatalog\Model\SharedCatalog as SharedCatalogEntity;

/**
 * Preconditions:
 * 1. Create store view.
 *
 * Steps:
 * 1. Open Admin.
 * 2. Go to Products > Shared Catalogs.
 * 3. Check public catalog name.
 * 4. Open shared catalog Edit.
 * 5. Change Type to Public.
 * 6. Click Proceed on popup.
 * 7. Click "Save" button.
 * 8. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68000
 */
class ChangeSharedCatalogTypeTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * SharedCatalog::CATALOG_PUBLIC
     */
    const CATALOG_PUBLIC = 'Public';

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var SharedCatalogCreate $sharedCatalogCreate
     */
    protected $sharedCatalogCreate;

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
     * Change SharedCatalog type.
     *
     * @param SharedCatalog $sharedCatalog
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog)
    {
        $sharedCatalog->persist();
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['type' => self::CATALOG_PUBLIC]);
        $publicName = $this->sharedCatalogIndex->getGrid()->getColumnValue(
            $this->sharedCatalogIndex->getGrid()->getFirstItemId(),
            'Name'
        );

        /** @var Edit $form */
        $customName = $sharedCatalog->getName();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $customName]);
        $this->sharedCatalogIndex->getGrid()->openEdit($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $this->sharedCatalogCreate->getSharedCatalogForm()->setType('Public');
        $this->sharedCatalogCreate->getModalBlock()->acceptAlert();
        $this->sharedCatalogCreate->getFormPageActions()->save();

        //Refresh cache popup
        $this->sharedCatalogIndex->getSystemMessageDialog()->closePopup();

        return [
            'sharedCatalogIndex' => $this->sharedCatalogIndex,
            'sharedCatalog' => $sharedCatalog,
            'publicName' => $publicName
        ];
    }
}
