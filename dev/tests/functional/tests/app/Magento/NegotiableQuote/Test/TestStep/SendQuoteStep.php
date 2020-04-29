<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Send a quote on storefront.
 */
class SendQuoteStep implements TestStepInterface
{
    /**
     * @var NegotiableQuoteGrid
     */
    private $quoteFrontendGrid;

    /**
     * @var NegotiableQuoteView
     */
    private $quoteFrontendView;

    /**
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendView
     */
    public function __construct(
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendView
    ) {
        $this->quoteFrontendGrid = $quoteFrontendGrid;
        $this->quoteFrontendView = $quoteFrontendView;
    }

    /**
     * Send a quote on storefront.
     *
     * @return array
     */
    public function run()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->send();

        return [
            'frontLock' => true
        ];
    }
}
