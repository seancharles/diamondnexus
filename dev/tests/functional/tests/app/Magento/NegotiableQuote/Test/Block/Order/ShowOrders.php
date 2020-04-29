<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;

/**
 * Class ShowOrders
 */
class ShowOrders extends Block
{
    /**
     * CSS selector for show my orders
     *
     * @var string
     */
    protected $showMyOrders = 'a:nth-child(1)';

    /**
     * Click show my orders link
     *
     * @return void
     */
    public function clickShowMyOrders()
    {
        $this->_rootElement->find($this->showMyOrders)->click();
    }
}
