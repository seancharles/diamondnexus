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
 * Save a quote as draft.
 */
class DraftQuoteStep implements TestStepInterface
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
     * @var string
     */
    private $discountType;

    /**
     * @var string
     */
    private $discountValue;

    /**
     * @var array
     */
    private $quote;

    /**
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $quote
     * @param string $discountType
     * @param string $discountValue
     */
    public function __construct(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $quote,
        $discountType,
        $discountValue
    ) {
        $this->negotiableQuoteGrid = $negotiableQuoteGrid;
        $this->negotiableQuoteView = $negotiableQuoteView;
        $this->quote = $quote;
        $this->discountType = $discountType;
        $this->discountValue = $discountValue;
    }

    /**
     * Save a quote as draft.
     *
     * @return array
     */
    public function run()
    {
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->open();
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView
            ->getQuoteDetails()
            ->fillDiscount($this->discountType, $this->discountValue);
        $this->negotiableQuoteView->getQuoteDetailsActions()->saveAsDraft();

        return [
            'frontStatus' => 'PENDING',
            'frontLock' => true,
            'disabledButtonsFront' => ['checkout', 'send', 'delete'],
            'disabledButtonsAdmin' => []
        ];
    }
}
