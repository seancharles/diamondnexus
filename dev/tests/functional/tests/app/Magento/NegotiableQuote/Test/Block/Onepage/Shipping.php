<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage;

use Magento\Mtf\Block\Block;

/**
 * Checkout shipping block.
 */
class Shipping extends Block
{
    /**
     * Selected shipping address selector
     *
     * @var string
     */
    protected $selectedShippingAddress = '.shipping-information-content';

    /**
     * Returns active shipping address.
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->_rootElement->find($this->selectedShippingAddress)->getText();
    }
}
