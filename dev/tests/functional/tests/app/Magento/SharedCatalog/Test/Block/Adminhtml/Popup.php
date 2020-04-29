<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Modal popup.
 */
class Popup extends Modal
{
    /**
     * Select text.
     */
    protected $textSelector = '.modal-content div';

    /**
     * Retrieve text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->_rootElement->find($this->textSelector)->getText();
    }

    /**
     * Check is visible popup.
     *
     * @return bool
     */
    public function isVisible()
    {
        return (bool)$this->_rootElement->isVisible();
    }
}
