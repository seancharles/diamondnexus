<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Order\Creditmemo;

/**
 * Credit memo create form.
 */
class Form extends \Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form
{
    /**
     * Refund Offline button css selector.
     *
     * @var string
     */
    protected $submitOffline = '[data-ui-id="order-items-submit-offline"]';

    /**
     * Submit credit memo offline.
     *
     * @return void
     */
    public function submitOffline()
    {
        $this->waitLoader();

        $this->_rootElement->find($this->submitOffline)->click();
    }
}
