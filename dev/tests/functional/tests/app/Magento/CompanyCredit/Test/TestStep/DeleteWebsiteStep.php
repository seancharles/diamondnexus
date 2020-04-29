<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Backend\Test\Page\Adminhtml\EditWebsite;
use Magento\Backend\Test\Page\Adminhtml\DeleteWebsite;
use Magento\Store\Test\Fixture\Website;

/**
 * Delete second website.
 */
class DeleteWebsiteStep implements \Magento\Mtf\TestStep\TestStepInterface
{
    /**
     * Stores list page.
     *
     * @var \Magento\Backend\Test\Page\Adminhtml\StoreIndex
     */
    private $storeIndex;

    /**
     * Edit website page.
     *
     * @var \Magento\Backend\Test\Page\Adminhtml\EditWebsite
     */
    private $editWebsite;

    /**
     * Delete website page.
     *
     * @var \Magento\Backend\Test\Page\Adminhtml\DeleteWebsite
     */
    private $deleteWebsite;

    /**
     * Website fixture.
     *
     * @var \Magento\Store\Test\Fixture\Website
     */
    private $website;

    /**
     * DeleteWebsiteStep constructor.
     *
     * @param StoreIndex $storeIndex
     * @param EditWebsite $editWebsite
     * @param DeleteWebsite $deleteWebsite
     * @param Website $website
     */
    public function __construct(
        StoreIndex $storeIndex,
        EditWebsite $editWebsite,
        DeleteWebsite $deleteWebsite,
        Website $website
    ) {
        $this->storeIndex = $storeIndex;
        $this->editWebsite = $editWebsite;
        $this->deleteWebsite = $deleteWebsite;
        $this->website = $website;
    }

    /**
     * Delete website.
     */
    public function run()
    {
        $this->storeIndex->open();
        $this->storeIndex->getStoreGrid()->searchAndOpenWebsite($this->website);
        $this->editWebsite->getFormPageActions()->delete();
        $this->deleteWebsite->getDeleteWebsiteForm()->fillForm(['create_backup' => 'No']);
        $this->deleteWebsite->getFormPageActions()->delete();
    }
}
