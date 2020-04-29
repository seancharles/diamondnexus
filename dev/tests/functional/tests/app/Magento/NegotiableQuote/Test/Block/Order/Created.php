<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;

/**
 * Class Created
 */
class Created extends Block
{
    /**
     * Get created at value
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->_rootElement->getText();
    }
}
