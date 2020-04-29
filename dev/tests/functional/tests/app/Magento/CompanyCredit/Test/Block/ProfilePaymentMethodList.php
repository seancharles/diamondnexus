<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block;

/**
 * Company payment method list block.
 */
class ProfilePaymentMethodList extends \Magento\Mtf\Block\Block
{
    /**
     * Selector for Payment Methods block.
     *
     * @var string
     */
    protected $paymentMethods = '.payment-methods-list';

    /**
     * Get available payment methods.
     *
     * @return array|string
     */
    public function getAvailablePaymentMethods()
    {
        return $this->_rootElement->find($this->paymentMethods)->getText();
    }
}
