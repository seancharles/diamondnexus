<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage;

use Magento\Mtf\Block\Block;

/**
 * Negotiable Quote discount block.
 */
class Discount extends Block
{
    /**
     * Css selector Add Coupon button.
     *
     * @var string
     */
    private $addGiftCardButton = '.action.apply.primary';

    /**
     * Css selector for coupon code input.
     *
     * @var string
     */
    private $discountInput = '#coupon_code';

    /**
     * Css selector for discount link.
     *
     * @var string
     */
    private $discountLink = '#block-discount-heading';

    /**
     * Add coupon code to quote.
     *
     * @param string $code
     * @return void
     */
    public function addCoupon($code)
    {
        $this->_rootElement->find($this->discountLink)->click();
        $this->_rootElement->find($this->discountInput)->setValue($code);
        $this->_rootElement->find($this->addGiftCardButton)->click();
    }
}
