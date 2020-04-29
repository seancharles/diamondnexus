<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Admin send a quote on backend.
 */
class AdminSendStep implements TestStepInterface
{
    /**
     * @var NegotiableQuoteIndex
     */
    private $negotiableQuoteGrid;

    /**
     * @var NegotiableQuoteView
     */
    private $negotiableQuoteView;

    /**
     * @var array
     */
    private $quote;

    /**
     * @var array
     */
    private $updateData;

    /**
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $quote
     * @param array $updateData
     */
    public function __construct(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $quote,
        array $updateData
    ) {
        $this->negotiableQuoteGrid = $negotiableQuoteGrid;
        $this->negotiableQuoteView = $negotiableQuoteView;
        $this->quote = $quote;
        $this->updateData = $updateData;
    }

    /**
     * Admin send a quote on backend.
     *
     * @return array
     */
    public function run()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
        $this->updateData['historyLog'] = array_merge($this->updateData['historyLog'], ['Expiration Date', 'Status']);

        return [
            'frontLock' => false,
            'disabledButtonsFront' => [],
            'disabledButtonsAdmin' => ['saveAsDraft', 'decline', 'send']
        ];
    }
}
