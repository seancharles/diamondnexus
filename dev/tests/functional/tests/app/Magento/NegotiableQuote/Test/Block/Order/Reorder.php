<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Reorder popup block on My Order page
 */
class Reorder extends Block
{
    /**
     * Replace items button
     *
     * @var string
     */
    protected $replaceItemsButton = '.action.replace';

    /**
     * Reorder popup
     *
     * @var string
     */
    protected $reorderPopup = '#reorder-quote-popup';

    /**
     * Click replace items button
     *
     * @return void
     */
    public function replaceItems()
    {
        $this->waitForElementVisible($this->reorderPopup);
        $this->_rootElement->find($this->replaceItemsButton)->click();
    }
}
