<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableCheckoutCart;

/**
 * Request a quote.
 */
class RequestQuoteStep implements TestStepInterface
{
    /**
     * Css selector for spinner in grid.
     *
     * @var string
     */
    private $spinner = '[data-role="spinner"]';

    /**
     * @var NegotiableCheckoutCart
     */
    private $cartPage;

    /**
     * @var NegotiableQuoteGrid
     */
    private $quoteFrontendGrid;

    /**
     * @param NegotiableCheckoutCart $cartPage
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     */
    public function __construct(
        NegotiableCheckoutCart $cartPage,
        NegotiableQuoteGrid $quoteFrontendGrid
    ) {
        $this->cartPage = $cartPage;
        $this->quoteFrontendGrid = $quoteFrontendGrid;
    }

    /**
     * Request a quote.
     *
     * @return array
     */
    public function run()
    {
        $quote = ['quote-name' => 'Quote' . time(), 'quote-message' => 'message'];
        $this->cartPage->open();
        $this->cartPage->getRequestQuote()->requestQuote();
        $this->cartPage->getRequestQuotePopup()->fillForm($quote);
        $expirationDate = new \DateTime('+30 days');
        $this->cartPage->getRequestQuotePopup()->submitQuote();
        $this->quoteFrontendGrid->getQuoteGrid()->waitForElementVisible($this->spinner);
        $this->quoteFrontendGrid->getQuoteGrid()->waitForElementNotVisible($this->spinner);

        return [
            'quote' => $quote,
            'expirationDate' => $expirationDate,
            'disabledButtonsFront' => ['checkout', 'delete'],
            'disabledButtonsAdmin' => [],
            'frontStatus' => 'SUBMITTED',
            'adminStatus' => 'Open',
            'frontLock' => false,
            'adminLock' => false
        ];
    }
}
