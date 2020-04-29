<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create shared catalog.
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Go to Products > Shared Catalogs.
 * 3. Filter Shared catalog by name.
 * 4. Select Shared catalog.
 * 5. Select Delete from Mass action menu.
 * 6. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-67982
 */
class DeleteSharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Delete SharedCatalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param array $data
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, $data = [])
    {
        $sharedCatalog->persist();
        $this->sharedCatalogIndex->open();
        if ($data['shouldSelect']) {
            if ($data['default']) {
                $this->sharedCatalogIndex->getGrid()->selectPublic();
            } else {
                $this->sharedCatalogIndex->getGrid()->searchAndSelect(['name' => $sharedCatalog->getName()]);
            }
        }
        $this->sharedCatalogIndex->getGrid()->clickMassDelete();
        if ($data['shouldSelect']) {
            $this->sharedCatalogIndex->getModalBlock()->acceptAlert();
        }
        return [
            'sharedCatalogIndex' => $this->sharedCatalogIndex,
            'sharedCatalog' => $sharedCatalog,
        ];
    }
}
