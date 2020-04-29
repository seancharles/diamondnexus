<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * System messages block.
 */
class SystemMessages extends Block
{
    /**
     * Css selector for system messages.
     *
     * @var string
     */
    private $systemMessagesBlock = '.message-system-short';

    /**
     * Wait for system messages displaying.
     *
     * @return void
     */
    public function waitForMessagesLoad()
    {
        $this->waitForElementNotVisible($this->systemMessagesBlock);
        $this->_rootElement->find($this->systemMessagesBlock)->click();
    }
}
