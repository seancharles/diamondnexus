<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Payment information block.
 */
class PaymentInformation extends Block
{
    /**
     * Css locator for payment title.
     */
    private $paymentTitle = '.order-payment-method-name';

    /**
     * Css locator for purchase order number.
     */
    private $poNumber = '.order-purchase-order-number';

    /**
     * Get payment method.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_rootElement->find($this->paymentTitle, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get purchase order number.
     *
     * @return string
     */
    public function getPurchaseOrderNumber()
    {
        return $this->_rootElement->find($this->poNumber, Locator::SELECTOR_CSS)->getText();
    }
}
