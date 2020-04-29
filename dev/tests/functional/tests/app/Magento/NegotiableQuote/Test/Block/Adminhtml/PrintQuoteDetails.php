<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

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
    protected $sku = '.product-sku-block';

    /**
     * CSS locator for expiration date field
     *
     * @var string
     */
    protected $expirationDateField = '.quote-information .quote-information-row:last-child span';

    /**
     * Css locator for qty inputs
     *
     * @var string
     */
    protected $qty = 'input.item-qty';

    /**
     * CSS locator for quote name
     *
     * @var string
     */
    protected $quoteName = '.quote_name';

    /**
     * CSS locator for quote status
     *
     * @var string
     */
    protected $quoteStatus = '.quote-information .quote-information-row:first-child span';

    /**
     * Totals rows css selector
     *
     * @var string
     */
    protected $totals = '.quote-negotiated-price .quote-subtotal-table tr';

    /**
     * Totals row price css selector
     *
     * @var string
     */
    protected $totalPrice = '.price';

    /**
     * Shipping address css selector
     *
     * @var string
     */
    protected $shippingMethod = '.admin__quote-shipment-methods-title';

    /**
     * Comments selector
     *
     * @var string
     */
    protected $comments = '.comments-block-item-comment';

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
            if (count($row->getElements($this->totalPrice))) {
                $totals[$row->getAttribute('class')] = $row->find($this->totalPrice)->getText();
            }
        }

        return $totals;
    }

    /**
     * Checks if method is correct
     *
     * @param string $method
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
     * Gets SKU list
     *
     * @return array
     */
    public function getSkuList()
    {
        $skuArr = [];
        $elements = $this->_rootElement->getElements($this->sku);
        foreach ($elements as $element) {
            $skuArr[] = str_replace(['SKU: ', PHP_EOL], '', $element->getText());
        }

        return $skuArr;
    }

    /**
     * Get expiration date
     *
     * @return string
     */
    public function getExpirationDate()
    {
        return trim($this->_rootElement->find($this->expirationDateField)->getText());
    }

    /**
     * Get qty list
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
     * Gets quote name
     *
     * @return string
     */
    public function getQuoteName()
    {
        return trim($this->_rootElement->find($this->quoteName)->getText());
    }

    /**
     * Gets quote status
     *
     * @return string
     */
    public function getQuoteStatus()
    {
        return trim($this->_rootElement->find($this->quoteStatus)->getText());
    }
}
