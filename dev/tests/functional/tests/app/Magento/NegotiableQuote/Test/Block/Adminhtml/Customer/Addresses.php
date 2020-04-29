<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Customer;

use Magento\Mtf\Block\Block;

/**
 * Class Addresses
 */
class Addresses extends Block
{
    /**
     * Open addresses block
     */
    public function openAddressesBlock()
    {
        $this->_rootElement->click();
    }
}
