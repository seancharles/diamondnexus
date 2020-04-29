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
 * Update a quote on storefront.
 */
class UpdateQuoteStep implements TestStepInterface
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
    private $updateData;

    /**
     * @var array
     */
    private $products;

    /**
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendView
     * @param array $updateData
     * @param array $products
     */
    public function __construct(
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendView,
        array $updateData,
        array $products
    ) {
        $this->quoteFrontendGrid = $quoteFrontendGrid;
        $this->quoteFrontendView = $quoteFrontendView;
        $this->updateData = $updateData;
        $this->products = $products;
    }

    /**
     * Update a quote on storefront.
     *
     * @return array
     */
    public function run()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->updateQuoteProductsQty($this->updateData['frontQtys']);
        $this->updateData['historyLog'][] = 'Status';
        foreach ($this->products as $product) {
            $this->updateData['historyLog'][] = $product->getName();
        }

        return [
            'disabledButtonsFront' => ['checkout', 'send'],
            'disabledButtonsAdmin' => [],
            'historyLog' => $this->updateData['historyLog']
        ];
    }
}
