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
 * Add a comment on negotiable quote in admin panel.
 */
class AddCommentOnBackendStep implements TestStepInterface
{
    /**
     * @var NegotiableQuoteIndex
     */
    private $negotiableQuoteGrid;

    /**
     * @var NegotiableQuoteEdit
     */
    private $negotiableQuoteView;

    /**
     * @var array
     */
    private $messages;

    /**
     * @var array
     */
    private $quote;

    /**
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $messages
     * @param array $quote
     */
    public function __construct(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $messages,
        array $quote
    ) {
        $this->negotiableQuoteGrid = $negotiableQuoteGrid;
        $this->negotiableQuoteView = $negotiableQuoteView;
        $this->messages = $messages;
        $this->quote = $quote;
    }

    /**
     * Add a comment on negotiable quote in admin panel.
     *
     * @return array
     */
    public function run()
    {
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->open();
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetails()->updateComment($this->messages['comment-admin']);
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();

        return [
            'disabledButtonsAdmin' => ['saveAsDraft', 'decline', 'send'],
            'frontStatus' => 'UPDATED',
            'adminLock' => true,
            'adminStatus' => 'Submitted'
        ];
    }
}
