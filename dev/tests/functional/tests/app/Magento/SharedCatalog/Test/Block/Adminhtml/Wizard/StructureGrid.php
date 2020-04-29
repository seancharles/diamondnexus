<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

/**
 * First step - structure grid of shared catalog configuration.
 */
class StructureGrid extends DataGrid
{
    /**
     * Css selector actions select.
     *
     * @var string
     */
    private $actionsSelect = 'th button';

    /**
     * Css selector for products grid loader.
     *
     * @var string
     */
    private $gridLoader = '#catalog-steps-wizard_step_structure .configure-step-right .admin__data-grid-loading-mask';

    /**
     * Xpath locator for dropdown option "Select All".
     *
     * @var string
     */
    private $actionsSelectOptionSelectAll =
        '//th/div/ul[@class="action-menu"]/li/span[contains(text(), "Select All")]';

    /**
     * Xpath locator for dropdown option "Deselect All".
     *
     * @var string
     */
    private $actionSelectOptionDeselectAll =
        '//th/div/ul[@class="action-menu"]/li/span[contains(text(), "Deselect All")]';

    /**
     * Css selector for switcher.
     *
     * @var string
     */
    private $switchCheckboxSelector = '.admin__actions-switch-checkbox';

    /**
     * Css selector for "Columns" panel button.
     *
     * @var string
     */
    private $columnsButton = '.admin__data-grid-action-columns .admin__action-dropdown';

    /**
     * Xpath selector for field in "Columns" panel.
     *
     * @var string
     */
    private $fieldColumnsPanel = '//div[contains(@class, "admin__data-grid-action-columns")]'
                               . '//*[contains(text(),"%s")]/parent::div';

    /**
     * Xpath selector for switch checkbox in "Columns" panel.
     *
     * @var string
     */
    private $switchCheckboxInColumnsBlock = './/div[contains(@class, "admin__data-grid-action-columns")]'
                                          .  '//*[contains(text(),"%s")]/preceding::input[1]';

    /**
     * Css selector for "Reset" button in "Columns" panel.
     *
     * @var string
     */
    private $resetColumnsButton = '.admin__action-dropdown-footer-secondary-actions button';

    /**
     * Css selector for columns title.
     *
     * @var string
     */
    private $columnsTitle = '.admin__data-grid-wrap th span.data-grid-cell-content';

    /**
     * Css selector for filter names.
     *
     * @var string
     */
    private $filtersTitle = '.admin__data-grid-filters > .admin__form-field:not([style*="none"]) > '
                          . '[class*="admin__form-field"]:first-child span';

    /**
     * Xpath selector for field from "Columns" panel.
     *
     * @var string
     */
    private $fieldsFromColumnsPanel = "./div[contains(@class, 'admin__data-grid-action-columns')][1]//label";

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '[name="sku"]',
        ],
    ];

    /**
     * Check item with switcher.
     *
     * @param array $filter [optional]
     * @return StructureGrid
     */
    public function checkSwitcherItem(array $filter = [])
    {
        $this->waitLoader();
        $this->search($filter);
        $this->waitLoader();
        $select = $this->_rootElement->find($this->actionsSelect);
        $select->click();
        $this->_rootElement->find($this->actionsSelectOptionSelectAll, Locator::SELECTOR_XPATH)->click();
        $this->waitLoader();
        return $this;
    }

    /**
     * Define if selection is selected.
     *
     * @param array $filter [optional]
     * @return bool
     */
    public function isSelectedItem(array $filter = [])
    {
        $this->waitLoader();
        $this->search($filter);
        $this->waitLoader();
        $rowItem = $this->getRow($filter);
        return $rowItem->find($this->switchCheckboxSelector)->isSelected();
    }

    /**
     * Uncheck item with switcher.
     *
     * @param array $filter [optional]
     * @return StructureGrid
     */
    public function uncheckSwitcherItem(array $filter = [])
    {
        $this->waitLoader();
        $this->search($filter);
        $this->waitLoader();
        $select = $this->_rootElement->find($this->actionsSelect);
        $select->click();
        $this->_rootElement->find($this->actionSelectOptionDeselectAll, Locator::SELECTOR_XPATH)->click();
        $this->waitLoader();
        return $this;
    }

    /**
     * Wait for loader.
     *
     * @return void
     */
    public function waitForLoader()
    {
        $this->waitForElementNotVisible($this->gridLoader);
    }

    /**
     * Click "Columns" button.
     *
     * @return void
     */
    public function clickColumnsButton()
    {
        $this->_rootElement->find($this->columnsButton)->click();
    }

    /**
     * Click "Reset" button in "Columns" panel.
     *
     * @return void
     */
    public function clickResetButton()
    {
        $this->clickColumnsButton();
        $this->_rootElement->find($this->resetColumnsButton)->click();
        $this->clickColumnsButton();
    }

    /**
     * Retrieve field from "Columns" panel.
     *
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function retrieveField($name)
    {
        return $this->_rootElement->find(sprintf($this->fieldColumnsPanel, $name), Locator::SELECTOR_XPATH);
    }

    /**
     * Check fields in "Columns" panel.
     *
     * @param array $fields
     * @return void
     */
    public function checkFieldsInColumnsPanel(array $fields)
    {
        $this->clickColumnsButton();
        foreach ($fields as $field) {
            $checkboxes = $this->_rootElement->getElements(
                sprintf($this->switchCheckboxInColumnsBlock, $field),
                Locator::SELECTOR_XPATH
            );
            $checkbox = current($checkboxes);
            if (!$checkbox->isSelected()) {
                $checkbox->click();
                sleep(3);
            }
        }
        $this->clickColumnsButton();
    }

    /**
     * Returns a list of columns names from product grid.
     *
     * @return array
     */
    public function getColumnsGrid()
    {
        $columns = [];
        foreach ($this->_rootElement->getElements($this->columnsTitle) as $column) {
            $columns[] = $column->getText();
        }
        return $columns;
    }

    /**
     * Returns a list of filter names from the filter panel.
     *
     * @return array
     */
    public function getFiltersTitle()
    {
        $filters = [];
        $this->_rootElement->find($this->filterButton)->click();
        foreach ($this->_rootElement->getElements($this->filtersTitle) as $filter) {
            $filters[] = $filter->getText();
        }
        $this->_rootElement->find($this->filterButton)->click();
        return $filters;
    }

    /**
     * Get fields from "Columns" panel.
     *
     * @return array
     */
    public function getFieldsFromColumnsPanel()
    {
        $fields = [];
        foreach ($this->_rootElement->getElements($this->fieldsFromColumnsPanel, Locator::SELECTOR_XPATH) as $field) {
            $fields[] = $field->getText();
        }
        return $fields;
    }
}
