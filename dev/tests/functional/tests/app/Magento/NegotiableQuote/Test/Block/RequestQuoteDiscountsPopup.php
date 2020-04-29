<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Class RequestQuoteDiscountsPopup
 * Request quote discount popup
 */
class RequestQuoteDiscountsPopup extends Block
{
    /**
     * CSS locator for request quote button
     *
     * @var string
     */
    protected $requestButton = '.modal-footer > button:nth-child(2)';

    /**
     * Click request quote
     *
     * @return void
     */
    public function acceptRequest()
    {
        $this->waitForElementVisible($this->requestButton);
        $this->_rootElement->find($this->requestButton)->click();
    }
}
