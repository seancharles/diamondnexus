<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class PrintQuoteDetails
 * Print quote details block
 */
class PrintQuoteDetails extends Block
{
    /**
     * CSS locator for sku
     *
     * @var string
     */
    protected $sku = 'td.col.sku';

    /**
     * CSS locator for qty
     *
     * @var string
     */
    protected $qty = 'td.col.qty input';

    /**
     * CSS locator for price
     *
     * @var string
     */
    protected $price = 'td.col.price';

    /**
     * CSS locator for expiration date
     *
     * @var string
     */
    protected $expirationDate = '.quote-date-expired date';

    /**
     * CSS locator for quote name
     *
     * @var string
     */
    protected $quoteName = '.quote-name';

    /**
     * CSS locator for status
     *
     * @var string
     */
    protected $status = '.quote-status';

    /**
     * Shipping address css selector
     *
     * @var string
     */
    protected $shippingMethod = '.box-order-shipping-method .box-content';

    /**
     * Totals rows css selector
     *
     * @var string
     */
    protected $totals = '.negotiated-price .quote-table-totals>tbody>tr';

    /**
     * Totals row price css selector
     *
     * @var string
     */
    protected $totalPrice = '.price';

    /**
     * Block with comments
     *
     * @var string
     */
    protected $comments = '.comments-block-item-comment';

    /**
     * Quote name block selector
     *
     * @var string
     */
    private $quoteNameBlockSelector = './/div[contains(@class,"quote-name") and contains(.,"%s")]';

    /**
     * Get comments
     *
     * @return array
     */
    public function getComments()
    {
        $comments = [];
        $rows = $this->_rootElement->getElements($this->comments);

        foreach ($rows as $row) {
            $comments[] = trim($row->getText());
        }

        return $comments;
    }

    /**
     * Returns array of quote totals
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            if (!$row->find($this->totalPrice)->isVisible()) {
                $totals[$row->getAttribute('class')] = '$0';
                continue;
            }
            $totals[$row->getAttribute('class')] = $row->find($this->totalPrice)->getText();
        }

        return $totals;
    }

    /**
     * Checks if method is correct
     *
     * @param $method
     * @return bool
     */
    public function isMethodCorrect($method)
    {
        if (!$method) {
            return true;
        }
        $text = trim($this->_rootElement->find($this->shippingMethod)->getText());
        return strpos($text, $method) !== false;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return trim($this->_rootElement->find($this->status)->getText());
    }

    /**
     * Gets quote name
     *
     * @return string
     */
    public function getQuoteName()
    {
        return trim($this->_rootElement->find($this->quoteName)->getText());
    }

    /**
     * Gets expiration date
     *
     * @return string
     */
    public function getExpirationDate()
    {
        return trim($this->_rootElement->find($this->expirationDate)->getText());
    }

    /**
     * Gets SKU list
     *
     * @return array
     */
    public function getSkuList()
    {
        $skuArr = [];
        $elements = $this->_rootElement->getElements($this->sku);
        foreach ($elements as $element) {
            $skuArr[] = $element->getText();
        }
        return $skuArr;
    }

    /**
     * Gets QTY list
     *
     * @return array
     */
    public function getQtyList()
    {
        $qtyArr = [];
        $elements = $this->_rootElement->getElements($this->qty);
        foreach ($elements as $element) {
            $qtyArr[] = $element->getValue();
        }
        return $qtyArr;
    }

    /**
     * Wait for quote name block to appear
     *
     * @param array $quote
     * @return void
     */
    public function waitForBlock(array $quote)
    {
        $this->waitForElementVisible(
            sprintf($this->quoteNameBlockSelector, $quote['quote-name']),
            Locator::SELECTOR_XPATH
        );
    }
}
