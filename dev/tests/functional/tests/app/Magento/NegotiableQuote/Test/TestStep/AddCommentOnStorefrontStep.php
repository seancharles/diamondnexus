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
 * Add a comment on negotiable quote in storefront.
 */
class AddCommentOnStorefrontStep implements TestStepInterface
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
     * @var array
     */
    private $messages;

    /**
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendView
     * @param array $messages
     */
    public function __construct(
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendView,
        array $messages
    ) {
        $this->quoteFrontendGrid = $quoteFrontendGrid;
        $this->quoteFrontendView = $quoteFrontendView;
        $this->messages = $messages;
    }

    /**
     * Add a comment on negotiable quote in storefront.
     *
     * @return array
     */
    public function run()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->updateComment($this->messages['comment']);
        $this->quoteFrontendView->getQuoteDetails()->send();

        return ['disabledButtonsFront' => []];
    }
}
