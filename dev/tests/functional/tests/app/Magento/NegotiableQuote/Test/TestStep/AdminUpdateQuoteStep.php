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
 * Update data a quote on backend.
 */
class AdminUpdateQuoteStep implements TestStepInterface
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
     * @var array
     */
    private $products;

    /**
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param array $quote
     * @param array $updateData
     * @param array $products
     */
    public function __construct(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        array $quote,
        array $updateData,
        array $products
    ) {
        $this->negotiableQuoteGrid = $negotiableQuoteGrid;
        $this->negotiableQuoteView = $negotiableQuoteView;
        $this->quote = $quote;
        $this->updateData = $updateData;
        $this->products = $products;
    }

    /**
     * Update a quote on backend.
     *
     * @return array
     */
    public function run()
    {
        $result = [
            'frontStatus' => 'UPDATED',
            'adminStatus' => 'Submitted',
            'disabledButtonsFront' => [],
            'disabledButtonsAdmin' => ['saveAsDraft', 'decline', 'send'],
            'adminLock' => true,
            'frontLock' => false,
            'proposedShippingPrice' =>
                isset($this->updateData['proposedShippingPrice']) ? $this->updateData['proposedShippingPrice'] : null,
            'discountType' =>
                isset($this->updateData['discountType']) ? $this->updateData['discountType'] : null,
            'discountValue' =>
                isset($this->updateData['discountValue']) ? $this->updateData['discountValue'] : null,
        ];
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->updateData['historyLog'][] = 'Status';
        if (isset($this->updateData['proposedShippingPrice'])) {
            $this->negotiableQuoteView
                ->getQuoteDetails()->fillProposedShippingPrice($this->updateData['proposedShippingPrice']);
            $result['method'] = 'Flat Rate';
            $this->updateData['historyLog'][] = 'Shipping Method';
            $this->updateData['historyLog'][] = 'Shipping Address';
        }
        if (isset($this->updateData['adminQtys'])) {
            $this->negotiableQuoteView->getQuoteDetails()->updateItems($this->updateData['adminQtys']);
            foreach ($this->products as $product) {
                $this->updateData['historyLog'][] = $product->getName();
            }
        }
        if (isset($this->updateData['expirationDate'])) {
            $expirationDate = new \DateTime($this->updateData['expirationDate']);
            $this->negotiableQuoteView->getQuoteDetails()->fillExpirationDate($expirationDate);
            $this->updateData['historyLog'][] = 'Expiration Date';
            $result['expirationDate'] = $expirationDate;
        }
        if (isset($this->updateData['discountType'])) {
            $this->negotiableQuoteView
                ->getQuoteDetails()
                ->fillDiscount($this->updateData['discountType'], $this->updateData['discountValue']);
        }
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
        $result['historyLog'] = $this->updateData['historyLog'];

        return $result;
    }
}
