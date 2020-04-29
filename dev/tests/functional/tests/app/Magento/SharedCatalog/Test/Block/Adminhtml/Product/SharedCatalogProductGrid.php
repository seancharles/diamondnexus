<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Product;

use Magento\Catalog\Test\Block\Adminhtml\Product\Grid;
use Magento\Mtf\Client\Locator;

/**
 * Grid with products list.
 */
class SharedCatalogProductGrid extends Grid
{
    /**
     * Action button (located above the Grid).
     *
     * @var string
     */
    protected $actionButton = 'button.action-select';

    /**
     * Xpath selector for field from "Columns" panel.
     *
     * @var string
     */
    private $fieldsFromColumnsPanel = "./div[contains(@class, 'admin__data-grid-action-columns')][1]//label";

    /**
     * Css selector for "Columns" panel button.
     *
     * @var string
     */
    private $columnsButton = '.admin__data-grid-action-columns .admin__action-dropdown';

    /**
     * Get fields from "Columns" panel.
     *
     * @return array
     */
    public function getFieldsFromColumnsPanel()
    {
        $this->waitLoader();
        $this->clickColumnsButton();
        $fields = [];
        foreach ($this->_rootElement->getElements($this->fieldsFromColumnsPanel, Locator::SELECTOR_XPATH) as $field) {
            $fields[] = $field->getText();
        }
        $this->clickColumnsButton();
        return $fields;
    }

    /**
     * Click "Columns" button.
     *
     * @return void
     */
    private function clickColumnsButton()
    {
        $this->_rootElement->find($this->columnsButton)->click();
    }
}
