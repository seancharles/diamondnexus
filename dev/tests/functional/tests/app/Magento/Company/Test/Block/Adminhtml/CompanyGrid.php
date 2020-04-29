<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Admin Data Grid for managing "Company" entities.
 */
class CompanyGrid extends DataGrid
{
    /**
     * Company row Xpath locator.
     *
     * @var string
     */
    protected $rowById = "//input[@data-action='select-row' and @value='%s']/parent::label/parent::td/parent::tr";

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'company_name' => [
            'selector' => '.admin__data-grid-filters input[name*=company_name]'
        ],
        'customer_group_id' => [
            'selector' => '.admin__data-grid-filters select[name*=customer_group_id]',
            'input' => 'select'
        ],
        'credit_limit_from' => [
            'selector' => '[name="credit_limit[from]"]',
        ],
        'credit_limit_to' => [
            'selector' => '[name="credit_limit[to]"]',
        ],
    ];

    /**
     * Columns button CSS selector.
     *
     * @var string
     */
    protected $columnsButton = '.admin__data-grid-action-columns .admin__action-dropdown';

    /**
     * Column checkbox Xpath locator.
     *
     * @var string
     */
    protected $columnCheckbox = '//div[contains(@class, "admin__data-grid-action-columns-menu")]' .
    '/div/div/label[contains(text(), "%s")]/preceding::input[1]';

    /**
     * Add column name to grid.
     *
     * @param string $columnName
     * @return void
     */
    public function addColumnByName($columnName)
    {
        $this->_rootElement->find($this->columnsButton)->click();
        $selector = sprintf($this->columnCheckbox, $columnName);
        if (!$this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isSelected()) {
            $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
        }

        $this->_rootElement->find($this->columnsButton)->click();
    }
}
