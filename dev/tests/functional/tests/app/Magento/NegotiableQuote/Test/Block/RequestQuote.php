<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Class RequestQuote
 * Request quote button
 */
class RequestQuote extends Block
{
    /**
     * CSS locator for request quote button
     *
     * @var string
     */
    protected $requestButton = '#negotiable-quote-form button';

    /**
     * Click request quote
     *
     * @return void
     */
    public function requestQuote()
    {
        $this->_rootElement->find($this->requestButton)->click();
    }
}
