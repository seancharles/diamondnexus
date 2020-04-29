<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Quote Grid block.
 */
class QuoteGrid extends Block
{
    /**
     * CSS locator for View first item link.
     *
     * @var string
     */
    private $firstView = '.action-menu-item';

    /**
     * CSS locator for View second item link.
     *
     * @var string
     */
    private $secondElement = 'tr.data-row:nth-child(2) > td:nth-child(7) > a:nth-child(1)';

    /**
     * Css locator for No data.
     *
     * @var string
     */
    private $noData = '.data-grid-tr-no-data';

    /**
     * Css locator for spinner.
     *
     * @var string
     */
    private $spinner = '.spinner';

    /**
     * CSS locator for Show My Quotes button.
     *
     * @var string
     */
    private $showMyQuotesButton = 'div.quote-grid-filters-wrap > button.action.action-secondary > span';

    /**
     * Css locator for loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Locator for quote View button.
     *
     * @var string
     */
    private $quoteView = './/tr[.//*[@title="%s"]]//a';

    /**
     * Open first quote in grid.
     *
     * @return void
     */
    public function openFirstItem()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible($this->firstView);
        $this->_rootElement->find($this->firstView)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Open requested item.
     *
     * @param array $quote
     * @return void
     */
    public function openItem(array $quote)
    {
        $this->_rootElement->find(sprintf($this->quoteView, $quote['quote-name']), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Open second quote in grid.
     *
     * @return void
     */
    public function openSecondItem()
    {
        $this->_rootElement->find($this->secondElement)->click();
    }

    /**
     * Checks is grid empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        $this->waitForElementNotVisible($this->spinner);
        return (bool)count($this->_rootElement->getElements($this->noData));
    }

    /**
     * Click Show My Quotes button.
     *
     * @return void
     */
    public function clickShowMyQuotesButton()
    {
        $this->_rootElement->find($this->showMyQuotesButton)->click();
        $this->waitForElementNotVisible($this->spinner);
    }
}
