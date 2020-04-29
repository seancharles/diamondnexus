<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Class QuoteShippingInformation
 */
class QuoteShippingInformation extends Block
{
    /**
     * @var string
     */
    private $shippingMethodLabel = '.quote-shipping-method .admin__page-section-item-title span';

    /**
     * Get shipping method label
     *
     * @return string
     */
    public function getShippingMethodLabel()
    {
        return trim($this->_rootElement->find($this->shippingMethodLabel)->getText());
    }
}
