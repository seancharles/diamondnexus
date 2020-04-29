<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Customer order view messages block.
 */
class Messages extends Block
{
    /**
     * Css selector for success message.
     *
     * @var string
     */
    private $successMessage = '[data-ui-id="message-success"]';

    /**
     * Css selector for error message.
     *
     * @var string
     */
    private $errorMessage = '[data-ui-id="message-error"]';

    /**
     * Wait for requisition list created success message.
     *
     * @return bool|null
     */
    public function waitForSuccessMessage()
    {
        return $this->waitForElementVisible($this->successMessage);
    }

    /**
     * Get error message which is present on the page.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $this->waitForElementVisible($this->errorMessage);

        return $this->_rootElement->find($this->errorMessage)->getText();
    }

    /**
     * Get success message which is present on the page.
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        $this->waitForElementVisible($this->successMessage);

        return $this->_rootElement->find($this->successMessage)->getText();
    }
}
