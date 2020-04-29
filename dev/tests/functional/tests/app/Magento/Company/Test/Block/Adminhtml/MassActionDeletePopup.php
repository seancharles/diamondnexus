<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Delete popup block.
 */
class MassActionDeletePopup extends Block
{
    /**
     * Confirm button.
     *
     * @var string
     */
    protected $confirmDeleteButton = '.action-primary.action-accept';

    /**
     * Cancel button.
     *
     * @var string
     */
    protected $cancelDeleteButton = '.action-secondary.action-dismiss';

    /**
     * Delete company.
     *
     * @return $this
     */
    public function confirmDelete()
    {
        if ($this->_rootElement->find($this->confirmDeleteButton)->isVisible()) {
            $this->_rootElement->find($this->confirmDeleteButton)->click();
        }

        return $this;
    }

    /**
     * Cancel delete company.
     *
     * @return $this
     */
    public function cancelDelete()
    {
        if ($this->_rootElement->find($this->cancelDeleteButton)->isVisible()) {
            $this->_rootElement->find($this->cancelDeleteButton)->click();
        }

        return $this;
    }
}
