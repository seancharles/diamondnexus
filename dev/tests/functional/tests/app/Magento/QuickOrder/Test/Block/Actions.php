<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Class Actions
 * Actions block
 */
class Actions extends Block
{
    /**
     * CSS locator for Add to Cart button
     *
     * @var string
     */
    protected $addToCartButton = 'button.primary';

    /**
     * Clicks Add To Cart
     */
    public function clickAddToCart()
    {
        $this->waitForElementVisible('button.tocart:not([disabled])');
        $this->_rootElement->find($this->addToCartButton)->click();
        $this->waitForElementVisible('span.qty>span.counter-number');
    }
}
