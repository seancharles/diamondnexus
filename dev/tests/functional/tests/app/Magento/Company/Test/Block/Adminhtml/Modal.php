<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Alert, confirm, prompt block.
 */
class Modal extends Block
{
    /**
     * Locator value for accept button.
     *
     * @var string
     */
    protected $acceptButtonSelector = '.action-primary.confirm';

    /**
     * Locator value for prompt input.
     *
     * @var string
     */
    protected $alertTextSelector = '.modal-inner-wrap [data-role=content] div';

    /**
     * Press OK on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function acceptAlert()
    {
        if ($this->_rootElement->find($this->acceptButtonSelector)->isVisible()) {
            $this->_rootElement->find($this->acceptButtonSelector)->click();
            $this->waitForElementNotVisible($this->acceptButtonSelector);
        }
    }

    /**
     * Get the alert dialog text.
     *
     * @return string
     */
    public function getAlertText()
    {
        return $this->_rootElement->find($this->alertTextSelector)->getText();
    }
}
