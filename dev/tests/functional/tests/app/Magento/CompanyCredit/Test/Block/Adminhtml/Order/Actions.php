<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Order actions block.
 */
class Actions extends Block
{
    /**
     * 'Cancel' button.
     *
     * @var string
     */
    protected $cancel = '[id$=cancel-button]';

    /**
     * Selector for confirmation popup title.
     *
     * @var string
     */
    protected $confirmModalTitle = '.confirm._show[data-role=modal] [data-role=title]';

    /**
     * Selector for confirmation popup title.
     *
     * @var string
     */
    protected $confirmModalMessage = '.confirm._show[data-role=modal] [data-role=content]';

    /**
     * Cancel order.
     *
     * @return void
     */
    public function clickCancel()
    {
        $this->_rootElement->find($this->cancel)->click();
    }

    /**
     * Get title of the confirmation popup.
     *
     * @return string
     */
    public function getConfirmationTitle()
    {
        return $this->browser->find($this->confirmModalTitle)->getText();
    }

    /**
     * Get message of the confirmation popup.
     *
     * @return string
     */
    public function getConfirmationMessage()
    {
        return $this->browser->find($this->confirmModalMessage)->getText();
    }
}
