<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage;

use Magento\Mtf\Block\Block;

/**
 * Negotiable Quote gift card block.
 */
class GiftCard extends Block
{
    /**
     * Add gift cards button.
     *
     * @var string
     */
    private $addGiftCardButton = '.action.action-add.primary';

    /**
     * Css selector for gift card input.
     *
     * @var string
     */
    private $giftCardsInput = '#giftcard-code';

    /**
     * Css selector for gift card link.
     *
     * @var string
     */
    private $giftCardLink = '#block-giftcard-heading';

    /**
     * Css selector for loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Fill gift card data in cart.
     *
     * @param string $code
     * @return void
     */
    public function addGiftCard($code)
    {
        $this->_rootElement->find($this->giftCardLink)->click();
        $this->_rootElement->find($this->giftCardsInput)->setValue($code);
        $this->_rootElement->find($this->addGiftCardButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }
}
