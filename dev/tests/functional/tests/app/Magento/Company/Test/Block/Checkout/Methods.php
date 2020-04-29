<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Checkout;

use Magento\Mtf\Block\Block;

/**
 * Checkout methods block
 */
class Methods extends Block
{
    /**
     * Selector for "Proceed to Checkout" button
     *
     * @var string
     */
    protected $checkoutButton = 'button[data-role="proceed-to-checkout"]';

    /**
     * Selector for "Check Out with Multiple Addresses" link
     *
     * @var string
     */
    protected $multipleAddressesButton = 'a.action.multicheckout';

    /**
     * Checks if "Proceed to Checkout" button is visible
     *
     * @return bool
     */
    public function isProceedToCheckoutVisible()
    {
        return $this->_rootElement->find($this->checkoutButton)->isVisible();
    }

    /**
     * Checks if "Check Out with Multiple Addresses" link is visible
     *
     * @return bool
     */
    public function isMultipleAddressesCheckoutVisible()
    {
        $this->_rootElement->find($this->multipleAddressesButton)->isVisible();
    }
}
