<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Order\Invoice\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Invoice totals block.
 */
class Totals extends Block
{
    /**
     * Order grand total xpath selector.
     *
     * @var string
     */
    private $grandTotal = '//tbody/tr/td/strong[contains(text(), "Grand Total (Excl.Tax)")]/../../td[2]/strong/span';

    /**
     * Order gift card amount xpath selector.
     *
     * @var string
     */
    private $giftCard = '//tbody/tr/td[contains(text(), "Gift Cards")]/../td[2]/span';

    /**
     * Order shipping amount xpath selector.
     *
     * @var string
     */
    private $shipping = '//tbody/tr/td[contains(text(), "Shipping & Handling")]/../td[2]/span';

    /**
     * Get order grand total value.
     *
     * @return string
     */
    public function getOrderGrandTotal()
    {
        return $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get gift card amount value.
     *
     * @return bool|string
     */
    public function getGiftCardAmount()
    {
        $giftCardAmount = $this->_rootElement->find($this->giftCard, Locator::SELECTOR_XPATH);

        if ($giftCardAmount->isVisible()) {
            return $giftCardAmount->getText();
        }

        return false;
    }

    /**
     * Get shipping amount value.
     *
     * @return string
     */
    public function getShippingAmount()
    {
        return $this->_rootElement->find($this->shipping, Locator::SELECTOR_XPATH)->getText();
    }
}
