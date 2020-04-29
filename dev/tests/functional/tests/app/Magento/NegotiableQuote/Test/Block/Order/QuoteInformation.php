<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;

/**
 * Quote information block on My Order page
 */
class QuoteInformation extends Block
{
    /**
     * Quote link
     *
     * @var string
     */
    protected $quoteLink = '.action.quote';

    /**
     * CSS selector for Created By field
     *
     * @var string
     */
    protected $createdBy = 'div.page-title-wrapper > div.order-date';

    /**
     * Returns quote link text
     *
     * @return string
     */
    public function getOrderQuoteText()
    {
        return $this->_rootElement->getText();
    }

    /**
     * Get created by text value
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->_rootElement->find($this->createdBy)->getText();
    }

    /**
     * Click quote link
     *
     * @return void
     */
    public function clickQuoteLink()
    {
        if ($this->_rootElement->find($this->quoteLink)->isVisible()) {
            $this->_rootElement->find($this->quoteLink)->click();
        }
    }
}
