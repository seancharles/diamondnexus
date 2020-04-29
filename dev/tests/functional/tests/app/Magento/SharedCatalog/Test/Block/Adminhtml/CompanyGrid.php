<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Locator;

/**
 * Shared catalog companies grid.
 */
class CompanyGrid extends DataGrid
{
    /** @var string */
    private $_actionLink = '.action-menu-item';

    /**
     * Css selector for columns titles.
     *
     * @var string
     */
    private $columnsTitle = '.admin__data-grid-wrap th span.data-grid-cell-content';

    /**
     * Css selector for "Columns" panel button.
     *
     * @var string
     */
    private $columnsButton = '.admin__data-grid-action-columns .admin__action-dropdown';

    /**
     * Xpath selector for first active filter label.
     *
     * @var string
     */
    private $firstActiveFilter = '//ul[@data-role="filter-list"]/li[1]/span[1]';

    /**
     * Css selector for filter names.
     *
     * @var string
     */
    private $filtersTitle = '.admin__data-grid-filters > .admin__form-field:not([style*="none"]) > ' .
    '[class*="admin__form-field"]:first-child span';

    /**
     * Xpath selector for options of "Assigned" filter.
     *
     * @var string
     */
    private $assignedFilterOptions = '//div[@class="admin__form-field-control"]/select[@name="is_current"]/option';

    /**
     * Xpath selector for fields from "Columns" panel.
     *
     * @var string
     */
    private $fieldsInColumnsPanel = "//div[contains(@class, 'admin__data-grid-action-columns')][1]//label";

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'company_name' => [
            'selector' => '[name="company_name"]',
        ],
        'is_current' => [
            'selector' => '[name="is_current"]',
            'input' => 'select'
        ],
    ];

    /**
     * Assign catalog on company.
     *
     * @param int $id
     * @return void
     */
    public function assignCatalog($id)
    {
        $row = $this->getRow(['entity_id' => $id]);
        $row->find($this->_actionLink)->click();
        $this->waitLoader();
    }

    /**
     * Get list of columns from "Columns" panel.
     *
     * @return array
     */
    public function getFieldsInColumnsPanel()
    {
        $this->clickColumnsButton();
        $columns = [];

        foreach ($this->_rootElement->getElements($this->fieldsInColumnsPanel, Locator::SELECTOR_XPATH) as $column) {
            $columns[] = $column->getText();
        }
        $this->clickColumnsButton();

        return $columns;
    }

    /**
     * Returns a list of columns names from product grid.
     *
     * @return array
     */
    public function getColumnsTitles()
    {
        $columns = [];
        foreach ($this->_rootElement->getElements($this->columnsTitle) as $column) {
            $columns[] = $column->getText();
        }
        return $columns;
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
     * Get first active filter label.
     *
     * @return string
     */
    public function getFirstActiveFilter()
    {
        $firstActiveFilter = $this->_rootElement->find($this->firstActiveFilter, Locator::SELECTOR_XPATH);
        return $firstActiveFilter->getText();
    }

    /**
     * Get options for "Assigned" column filter.
     *
     * @return array
     */
    public function getAssignFilterOptions()
    {
        $options = [];
        $this->_rootElement->find($this->filterButton)->click();
        foreach ($this->_rootElement->getElements($this->assignedFilterOptions, Locator::SELECTOR_XPATH) as $option) {
            $options[] = $option->getText();
        }
        $this->_rootElement->find($this->filterButton)->click();
        return $options;
    }

    /**
     * Returns a list of filter names from the filter panel.
     *
     * @return array
     */
    public function getFiltersTitles()
    {
        $filters = [];
        $this->_rootElement->find($this->filterButton)->click();
        foreach ($this->_rootElement->getElements($this->filtersTitle) as $filter) {
            $filters[] = $filter->getText();
        }
        $this->_rootElement->find($this->filterButton)->click();
        return $filters;
    }
}
