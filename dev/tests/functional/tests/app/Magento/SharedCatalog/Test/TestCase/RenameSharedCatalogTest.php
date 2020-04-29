<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;

/**
 * Preconditions:
 * 1. Create shared catalog.
 *
 * Steps:
 * 1. Rename shared catalog.
 * 2. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68619
 */
class RenameSharedCatalogTest extends Injectable
{
    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate
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
     * Rename shared catalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param string $defaultSharedCatalogName
     *
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        $defaultSharedCatalogName
    ) {
        $sharedCatalog->persist();
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->openEdit($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogName = $this->getSharedCatalogName();
        $this->sharedCatalogCreate->getSharedCatalogForm()->setName($sharedCatalogName);
        $this->sharedCatalogCreate->getFormPageActions()->save();

        return [
            'customerGroupCode' => $sharedCatalogName,
            'sharedCatalogName' => $sharedCatalogName,
            'customerGroupFilterOptions' => [$defaultSharedCatalogName, $sharedCatalogName]
        ];
    }

    /**
     * Prepare unique shared catalog name.
     *
     * @return string
     */
    private function getSharedCatalogName()
    {
        return 'Shared Catalog ' . time();
    }
}
