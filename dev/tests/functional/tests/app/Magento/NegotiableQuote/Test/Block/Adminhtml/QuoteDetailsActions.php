<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Quote Details Actions block.
 */
class QuoteDetailsActions extends Block
{
    /**
     * Save as draft button CSS selector.
     *
     * @var string
     */
    private $saveButton = '#quote_save';

    /**
     * Decline button CSS selector.
     *
     * @var string
     */
    private $declineButton = '#quote-view-decline-button';

    /**
     * OK button css selector.
     *
     * @var string
     */
    private $okButton = '.confirm.action-primary.action-accept';

    /**
     * Send button css selector.
     *
     * @var string
     */
    private $sendButton = '#quote_send';

    /**
     * Success css selector.
     *
     * @var string
     */
    private $success = '.message.message-success.success';

    /**
     * Disabled buttons css selectors.
     *
     * @var array
     */
    private $disabledButtons = [
        'saveAsDraft' => '#quote_save.disabled',
        'decline' => '#quote-view-decline-button.disabled',
        'send' => '#quote_send.disabled',
    ];

    /**
     * Print button css selector.
     *
     * @var string
     */
    private $printButton = '#quote_print';

    /**
     * Css locator for loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Selector for initial script.
     *
     * @var string
     */
    private $initialScript = 'script[type="text/x-magento-init"]';

    /**
     * Click save as draft button.
     *
     * @return void
     */
    public function saveAsDraft()
    {
        $this->_rootElement->find($this->saveButton)->click();
    }

    /**
     * Decline quote.
     *
     * @return void
     */
    public function decline()
    {
        $this->_rootElement->find($this->declineButton)->click();
    }

    /**
     * Send quote.
     *
     * @return void
     */
    public function send()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->initialScript);
        $this->_rootElement->find($this->sendButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Checks if buttons are disabled.
     *
     * @param array $buttons
     * @return bool
     */
    public function areButtonsDisabled(array $buttons)
    {
        $result = true;
        foreach ($buttons as $button) {
            $result = (bool)count($this->_rootElement->getElements($this->disabledButtons[$button]));
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Click print button.
     *
     * @return void
     */
    public function clickPrint()
    {
        $this->waitForElementVisible($this->printButton);
        $this->_rootElement->find($this->printButton)->click();
    }
}
