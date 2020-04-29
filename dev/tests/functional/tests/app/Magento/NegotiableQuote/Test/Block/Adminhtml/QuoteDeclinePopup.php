<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Quote decline popup.
 */
class QuoteDeclinePopup extends Block
{
    /**
     * Decline message css selector.
     *
     * @var string
     */
    private $declineMessage = 'div.decline-message';

    /**
     * Confirm button css selector.
     *
     * @var string
     */
    private $confirmDeclineButton = '.modal-popup._show .confirm.action-primary.action-accept';

    /**
     * Reason textarea css selector.
     *
     * @var string
     */
    private $reasonTextareaSelector = '#reason-textarea';

    /**
     * Get decline message text.
     *
     * @return string
     */
    public function getNotificationMessage()
    {
        $this->waitForElementVisible($this->declineMessage);
        return $this->_rootElement->find($this->declineMessage)->getText();
    }

    /**
     * Fill decline form.
     *
     * @param string $reason
     * @return $this
     */
    public function fillDeclineReason($reason)
    {
        $this->waitForElementVisible($this->reasonTextareaSelector);
        $this->_rootElement->find($this->reasonTextareaSelector)->setValue($reason);

        return $this;
    }

    /**
     * Submit a quote.
     *
     * @return $this
     */
    public function confirmDecline()
    {
        $this->waitForElementVisible($this->confirmDeclineButton);
        $this->_rootElement->find($this->confirmDeclineButton)->click();

        return $this;
    }
}
