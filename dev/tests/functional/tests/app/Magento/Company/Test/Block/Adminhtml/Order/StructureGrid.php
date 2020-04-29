<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Order;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

/**
 * Grid with advanced functionality for managing entities.
 */
class StructureGrid extends DataGrid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'company_name' => [
            'selector' => '[name="company_name"]',
        ]
    ];

    /**
     * Xpath selector for previous column title.
     *
     * @var string
     */
    private $previousColumnTitle = './/*[@data-role="grid-wrapper"]'
                                 . '//th/span[contains(text(),"%s")]/../preceding::span[1]';

    /**
     * Xpath selector for previous filter title.
     *
     * @var string
     */
    private $previousFilterTitle = '//div[contains(@class, "admin__form-field")]'
                                .  '//span[contains(text(),"%s")]/../../preceding::label[1]';

    /**
     * "Columns" menu button CSS selector.
     *
     * @var string
     */
    private $columnsMenu = '.admin__action-dropdown-menu.admin__data-grid-action-columns-menu';

    /**
     * Xpath selector for menu button.
     *
     * @var string
     */
    private $openMenuButton = '//div[contains(@class, "admin__data-grid-actions-wrap")] '
                            . '//span[contains(text(),"%s")]';

    /**
     * Css selector for "Reset" button in "Columns" menu.
     *
     * @var string
     */
    private $resetColumnsButton = '.admin__data-grid-action-columns '
                                . '.admin__action-dropdown-footer-secondary-actions button';

    /**
     * Xpath selector for previous field in "Columns" menu.
     *
     * @var string
     */
    private $previousTitleFieldInColumnsMenu = '//div[contains(@class, "admin__data-grid-action-columns")]'
                                             . '//label[contains(text(),"%s")]/parent::div/preceding::div[1]/label';

    /**
     * Xpath selector for field in "Columns" menu.
     *
     * @var string
     */
    private $fieldInColumnsMenu = '//div[contains(@class, "admin__data-grid-action-columns")]'
                                . '//*[contains(text(),"%s")]/parent::div';

    /**
     * Xpath selector for switch checkbox in "Columns" menu.
     *
     * @var string
     */
    private $switchCheckboxFieldInColumnsMenu = '//div[contains(@class, "admin__data-grid-action-columns")]'
                                              . '//*[contains(text(),"%s")]/preceding::input[1]';

    /**
     * Css selector for loader in grid.
     *
     * @var string
     */
    private $gridLoader = '.admin__data-grid-loading-mask';

    /**
     * Css selector for spinner.
     *
     * @var string
     */
    private $spinner = '[data-role="spinner"]';

    /**
     * @var string
     */
    private $columnsButtonLabel = "Columns";

    /**
     * Retrieve previous field title in "Columns" menu.
     *
     * @param string $title
     * @return string
     */
    public function retrievePreviousTitleFieldInColumnsMenu($title)
    {
        $this->openColumnsMenu();
        return $this->_rootElement
            ->find(sprintf($this->previousTitleFieldInColumnsMenu, $title), Locator::SELECTOR_XPATH)
            ->getText();
    }

    /**
     * Retrieve previous field title in "Columns" menu.
     *
     * @param string $title
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function retrieveFieldInColumnsMenu($title)
    {
        $this->openColumnsMenu();
        return $this->_rootElement
            ->find(sprintf($this->fieldInColumnsMenu, $title), Locator::SELECTOR_XPATH);
    }

    /**
     * Check fields in "Columns" menu.
     *
     * @param string $titles
     * @return void
     */
    public function checkFieldsInColumnsMenu($titles)
    {
        $this->openColumnsMenu();
        $titles = explode(', ', $titles);
        foreach ($titles as $title) {
            $checkbox = $this->_rootElement->find(
                sprintf($this->switchCheckboxFieldInColumnsMenu, $title),
                Locator::SELECTOR_XPATH
            );
            if (!$checkbox->isSelected()) {
                $checkbox->click();
            }
        }

        $this->_rootElement
            ->find(sprintf($this->openMenuButton, $this->columnsButtonLabel), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Click "Reset" button in "Columns" menu.
     *
     * @return void
     */
    public function clickResetButton()
    {
        $this->openColumnsMenu();
        $this->_rootElement->find($this->resetColumnsButton)->click();
        $this->_rootElement
            ->find(sprintf($this->openMenuButton, $this->columnsButtonLabel), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Open "Columns" menu.
     *
     * @return void
     */
    private function openColumnsMenu()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementNotVisible($this->gridLoader);
        if (!$this->_rootElement->find($this->columnsMenu)->isVisible()) {
            $this->_rootElement
                ->find(sprintf($this->openMenuButton, $this->columnsButtonLabel), Locator::SELECTOR_XPATH)
                ->click();
        }
    }

    /**
     * Retrieve previous title field in "Columns" menu.
     *
     * @param string $title
     * @return string
     */
    public function retrievePreviousColumnTitle($title)
    {
        $this->waitForElementNotVisible($this->spinner);
        return $this->_rootElement
            ->find(sprintf($this->previousColumnTitle, $title), Locator::SELECTOR_XPATH)
            ->getText();
    }

    /**
     * Retrieve previous title field in "Columns" menu.
     *
     * @param string $title
     * @return string
     */
    public function retrievePreviousFilterTitle($title)
    {
        $this->openFilterBlock();
        return $this->_rootElement
            ->find(sprintf($this->previousFilterTitle, $title), Locator::SELECTOR_XPATH)
            ->getText();
    }
}
