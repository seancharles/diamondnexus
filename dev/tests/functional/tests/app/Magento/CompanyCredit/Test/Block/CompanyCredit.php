<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block;

use Magento\Mtf\Client\Locator;

/**
 * Class CompanyCredit.
 */
class CompanyCredit extends \Magento\Mtf\Block\Block
{
    /**
     * Column number in grid credit history on storefront.
     *
     * @var int
     */
    private $columnOperation = 1;

    /**
     * Outstanding Balance text.
     *
     * @var string
     */
    protected $outstandingBalance = 'Outstanding Balance';

    /**
     * Available Credit text.
     *
     * @var string
     */
    protected $availableCredit = 'Available Credit';

    /**
     * Credit Limit text.
     *
     * @var string
     */
    protected $creditLimit = 'Credit Limit';

    /**
     * Balance Xpath selector.
     *
     * @var string
     */
    protected $balanceSelector = '//ul[contains(@class, "credit-balance-list")]/li/p[contains(text(),"%s")]/../span';

    /**
     * Credit History CSS selector.
     *
     * @var string
     */
    protected $creditHistoryTable = 'div.data-grid-wrap.table-wrapper > table > tbody > tr';

    /**
     * Css locator for spinner.
     *
     * @var string
     */
    protected $spinner = '.spinner';

    /**
     * Get Outstanding Balance value.
     *
     * @return float|null
     */
    public function getOutstandingBalance()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible(
            sprintf($this->balanceSelector, $this->outstandingBalance),
            Locator::SELECTOR_XPATH
        );
        $value = $this->_rootElement->find(
            sprintf($this->balanceSelector, $this->outstandingBalance),
            Locator::SELECTOR_XPATH
        )->getText();
        return $value === '' ? null : (float)preg_replace("/[^\-\.0-9]/", "", $value);
    }

    /**
     * Get Available Credit value.
     *
     * @return float|null
     */
    public function getAvailableCredit()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible(
            sprintf($this->balanceSelector, $this->availableCredit),
            Locator::SELECTOR_XPATH
        );
        $value = $this->_rootElement->find(
            sprintf($this->balanceSelector, $this->availableCredit),
            Locator::SELECTOR_XPATH
        )->getText();
        return $value === '' ? null : (float)preg_replace("/[^\-\.0-9]/", "", $value);
    }

    /**
     * Get Credit Limit value.
     *
     * @return float|null
     */
    public function getCreditLimit()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible(
            sprintf($this->balanceSelector, $this->creditLimit),
            Locator::SELECTOR_XPATH
        );
        $value = $this->_rootElement->find(
            sprintf($this->balanceSelector, $this->creditLimit),
            Locator::SELECTOR_XPATH
        )->getText();
        return $value === '' ? null : (float)preg_replace("/[^\-\.0-9]/", "", $value);
    }

    /**
     * Get Credit History value.
     *
     * @return array
     */
    public function getHistory()
    {
        $data = [];
        $this->waitForElementNotVisible($this->spinner);
        $rows = $this->_rootElement->getElements($this->creditHistoryTable);

        foreach ($rows as $row) {
            $rowCells = explode("\n", trim($row->getText()));
            $data[$rowCells[$this->columnOperation]] = $rowCells;
        }

        return $data;
    }
}
