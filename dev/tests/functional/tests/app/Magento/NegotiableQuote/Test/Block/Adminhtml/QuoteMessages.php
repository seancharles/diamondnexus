<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Quote messages block.
 */
class QuoteMessages extends Block
{
    /**
     * Css selector for messages container.
     *
     * @var string
     */
    private $messagesContainer = '.wrap-messages';

    /**
     * Css selector for warning message.
     *
     * @var string
     */
    private $warningMessage = '.message.message-warning div';

    /**
     * Get warning messages list.
     *
     * @return array
     */
    public function getWarningMessages()
    {
        $messages = [];

        $this->waitForElementVisible($this->messagesContainer . ' ' . $this->warningMessage);
        $quoteMessages = $this->_rootElement->getElements($this->warningMessage);

        foreach ($quoteMessages as $message) {
            $messages[] = trim($message->getText());
        }

        return $messages;
    }
}
