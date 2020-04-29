<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class ShippingAddress
 */
class ShippingAddress extends Block
{
    /**
     * @var string
     */
    protected $messageSelector = '.message.notice';

    /**
     * Xpath selector for shipping address select button
     *
     * @var string
     */
    protected $shippingAddressSelector = '//div[@class="shipping-address-items"]/div[contains(., "%s")]/button';

    /**
     * Get message
     *
     * @return array|string
     */
    public function getMessage()
    {
        return $this->_rootElement->find($this->messageSelector)->getText();
    }

    /**
     * Select shipping address
     *
     * @param string $regionName
     * @return void
     */
    public function selectShippingAddress($regionName)
    {
        $this->_rootElement->find(
            sprintf($this->shippingAddressSelector, $regionName),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
