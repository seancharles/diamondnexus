<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Quote information block on Order page.
 */
class QuoteInformation extends Block
{
    /**
     * Quote info block xpath selector.
     *
     * @var string
     */
    private $quoteInfoBlock = '//table[@class="admin__table-secondary order-information-table"]/tbody/tr'
        . '/th[contains(text(), "Order Placed From Quote")]/parent::tr';

    /**
     * Quote link xpath selector.
     *
     * @var string
     */
    private $quoteLink = '//table[@class="admin__table-secondary order-information-table"]/tbody/tr'
        . '/th[contains(text(), "Order Placed From Quote")]/parent::tr/td/a';

    /**
     * Returns quote link text.
     *
     * @return string
     */
    public function getOrderQuoteText()
    {
        return $this->_rootElement->find($this->quoteInfoBlock, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Click quote link.
     *
     * @return void
     */
    public function clickQuoteLink()
    {
        $quoteLink = $this->_rootElement->find($this->quoteLink, Locator::SELECTOR_XPATH);

        if ($quoteLink->isVisible()) {
            $quoteLink->click();
        }
    }
}
