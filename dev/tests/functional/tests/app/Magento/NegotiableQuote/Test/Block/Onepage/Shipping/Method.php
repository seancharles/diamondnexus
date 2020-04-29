<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage\Shipping;

use Magento\Mtf\Block\Block;

/**
 * Checkout shipping method block.
 */
class Method extends Block
{
    /**
     * Selector for active shipping method.
     *
     * @var string
     */
    protected $selectedShippingMethod = '.shipping-information-content';

    /**
     * Get shipping method
     *
     * @return array|string
     */
    public function getShippingMethod()
    {
        return $this->_rootElement->find($this->selectedShippingMethod)->getText();
    }
}
