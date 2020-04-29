<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\SharedCatalog\Model\SharedCatalog;

/**
 * Shared catalog list.
 */
class SharedCatalogGrid extends DataGrid
{
    /**
     * SharedCatalog::CATALOG_PUBLIC
     */
    const CATALOG_PUBLIC = 'Public';

    /**
     * @var string
     */
    protected $selectButton = '//*[@title="Select Items"]';

    /**
     * @var string
     */
    protected $actionMenu = '.action-menu';

    /**
     * @var string
     */
    protected $editLink = 'a[data-action=\'item-edit\']';

    /**
     * @var string
     */
    protected $deleteLink = 'a[data-action=\'item-delete\']';

    /**
     * @var string
     */
    protected $configureLink = 'a[data-action=\'item-configure\']';

    /**
     * @var string
     */
    protected $headerActions = '.admin__data-grid-header-row.row-gutter';

    /**
     * @var string
     */
    protected $checkbox = '//label[contains(.,normalize-space("%s"))]//..//input';

    /**
     * Locator for 'Sort' link.
     *
     * @var string
     */
    protected $sortFindLink = "//th/span[contains(text(), '%s')]";

    /**
     * @var string
     */
    protected $actionType = 'Delete';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'type' => [
            'selector' => '//label[span[text()="Type"]]/following-sibling::div',
            'strategy' => 'xpath',
            'input' => 'dropdownmultiselect'
        ],
        'name' => [
            'selector' => '[name="name"]',
        ],
    ];

    /**
     * Search item using Data Grid Filter.
     *
     * @param array $filter
     * @return void
     */
    public function search(array $filter)
    {
        $this->waitLoader();
        parent::search($filter);
    }

    /**
     * Opens shared catalog for edit
     *
     * @param int $id
     * @return void
     */
    public function openEdit($id)
    {
        $this->openCustom($id, 'General Settings');
    }

    /**
     * Opens shared catalog configuration
     *
     * @param int $id
     * @return void
     */
    public function openConfigure($id)
    {
        $this->openCustom($id, 'Set Pricing and Structure');
    }

    /**
     * Opens shared catalog companies
     *
     * @param int $id
     * @return void
     */
    public function openCompanies($id)
    {
        $this->openCustom($id, 'Assign Companies');
    }

    /**
     * Open custom action menu
     *
     * @param int $id
     * @param string $action
     * @return void
     */
    protected function openCustom($id, $action)
    {
        $row = $this->getRow(['entity_id' => $id]);
        $row->find('button.action-select')->click();
        $this->waitForElementVisible('.action-menu._active');
        $row->find($action, Locator::SELECTOR_LINK_TEXT)->click();
    }

    /**
     * Select mass delete
     *
     * @return void
     */
    public function clickMassDelete()
    {
        $this->waitLoader();
        $this->_rootElement
            ->find($this->headerActions)
            ->find($this->selectButton, Locator::SELECTOR_XPATH)->click();
        $this->_rootElement
            ->find($this->headerActions)
            ->find(sprintf($this->massActionToggleList, $this->actionType), Locator::SELECTOR_XPATH)
            ->click();
    }

    /**
     * Select public catalog
     *
     * @return void
     */
    public function selectPublic()
    {
        $this->waitLoader();
        $this->resetFilter();
        $this->sortGridByField('Type', 'desc');
        $this->selectItems([['type' => self::CATALOG_PUBLIC]]);
    }

    /**
     * Sort grid by field.
     *
     * @param string $field
     * @param string $sort
     * @return void
     */
    public function sortGridByField($field, $sort = "desc")
    {
        $sortClass = $sort == 'asc' ? '_ascend' : '_descend';
        $sortBlock = $this->_rootElement->find(sprintf($this->sortLink, $sortClass, $field), Locator::SELECTOR_XPATH);
        if (!$sortBlock->isVisible()) {
            $this->_rootElement->find(sprintf($this->sortFindLink, $field), Locator::SELECTOR_XPATH)->click();
            $this->waitLoader();
        }
    }
}
