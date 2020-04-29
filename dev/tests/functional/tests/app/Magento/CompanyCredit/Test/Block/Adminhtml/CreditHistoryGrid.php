<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

/**
 * Credit history grid in Company Credit section of company edit page.
 */
class CreditHistoryGrid extends DataGrid
{
    /**
     * XPath locator for operation in credit balance history grid.
     *
     * @var string
     */
    private $balanceHistoryRow = '//*[@data-index="company_credit"]//td//div[contains(text(),\'%s\')]/ancestor::tr';

    /**
     * Xpath locator for "Edit" Link.
     *
     * @var string
     */
    private $reimbursedEdit = '//*[@data-index="company_credit"]//tr//a[contains(text(),\'Edit\')]';

    /**
     * Xpath locator for oder link.
     *
     * @var string
     */
    private $orderLink = '//*[@data-index="company_credit"]//td'
    . '//div[contains(text(),\'Purchased\')]/ancestor::tr//a[contains(text(),\'%s\')]';

    /**
     * Xpath locator for company credit history grid loader.
     *
     * @var string
     */
    private $creditHistoryGridSpinner = '//div[contains(@data-component, "history_listing_columns")]';

    /**
     * Filters.
     *
     * @var array
     */
    protected $filters = [
        'operation' => [
            'selector' => '[name="type"]',
            'input' => 'select'
        ]
    ];

    /**
     * Xpath locator grid field.
     *
     * @var string
     */
    private $gridField = '//tr[1]/td[count(//th[span[.="%s"]]/preceding-sibling::th)+1]/div';

    /**
     * Get field value.
     *
     * @param string $columnLabel
     * @return string
     */
    public function getFirstRowGridValue($columnLabel)
    {
        return trim(
            $this->_rootElement->find(sprintf($this->gridField, $columnLabel), Locator::SELECTOR_XPATH)->getText()
        );
    }

    /**
     * Get credit balance history row by operation type.
     *
     * @param string $operation
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getCreditBalanceHistoryRow($operation)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->waitForElementNotVisible($this->creditHistoryGridSpinner, Locator::SELECTOR_XPATH);
        $this->waitForElementVisible(sprintf($this->balanceHistoryRow, $operation), Locator::SELECTOR_XPATH);
        return $this->_rootElement->find(sprintf($this->balanceHistoryRow, $operation), Locator::SELECTOR_XPATH);
    }

    /**
     * Is credit balance history row by operation type visible.
     *
     * @param string $operation
     * @return bool
     */
    public function isCreditBalanceHistoryRowVisible($operation)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->waitForElementNotVisible($this->creditHistoryGridSpinner, Locator::SELECTOR_XPATH);
        $row = $this->_rootElement->find(
            sprintf($this->balanceHistoryRow, $operation),
            Locator::SELECTOR_XPATH
        );

        return $row->isVisible();
    }

    /**
     * Click link "Edit".
     *
     * @return void
     */
    public function clickReimbursedEditLink()
    {
        $this->getTemplateBlock()->waitLoader();
        $this->waitForElementNotVisible($this->creditHistoryGridSpinner, Locator::SELECTOR_XPATH);
        $this->waitForElementVisible($this->reimbursedEdit, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($this->reimbursedEdit, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get order link from company credit history grid.
     *
     * @param string $id
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getOrderLink($id)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->waitForElementNotVisible($this->creditHistoryGridSpinner, Locator::SELECTOR_XPATH);
        $this->waitForElementVisible(sprintf($this->orderLink, $id), Locator::SELECTOR_XPATH);
        return $this->_rootElement->find(sprintf($this->orderLink, $id), Locator::SELECTOR_XPATH);
    }
}
