<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Quote decline restriction popup.
 */
class QuoteDeclineRestrictionPopup extends Block
{
    /**
     * Confirm button css selector.
     *
     * @var string
     */
    private $confirmDeclineButton = '.confirm.action-primary.action-accept';

    /**
     * Confirm quote decline.
     *
     * @return $this
     */
    public function confirmDecline()
    {
        $confirmButton = $this->_rootElement->find($this->confirmDeclineButton);

        if ($confirmButton->isVisible()) {
            $confirmButton->click();
        }

        return $this;
    }
}
