<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyPayment\Test\Block\Adminhtml\Order\Invoice;

use Magento\Mtf\Client\Locator;

/**
 * Invoice totals block.
 */
class Totals extends \Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Totals
{
    /**
     * Capture amount is visible.
     *
     * @return bool
     */
    public function captureIsVisible()
    {
        return $this->_rootElement->find($this->capture, Locator::SELECTOR_CSS)->isVisible();
    }
}
