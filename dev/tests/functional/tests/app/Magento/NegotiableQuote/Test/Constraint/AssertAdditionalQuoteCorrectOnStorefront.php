<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that quote contains right data.
 *
 * @SuppressWarnings(PHPMD)
 */
class AssertAdditionalQuoteCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $products
     * @param array $qtys
     * @param int $tax
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $products,
        array $qtys,
        $tax
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openSecondItem();

        $this->checkNotificationMessage($negotiableQuoteView);
        $this->checkQuoteGrandTotal(
            $products,
            $qtys,
            $tax,
            $negotiableQuoteView
        );
    }

    /**
     * Check quote grand total
     *
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function checkQuoteGrandTotal(
        $products,
        $qtys,
        $tax,
        NegotiableQuoteView $negotiableQuoteView
    ) {
        $totals = $negotiableQuoteView->getQuoteDetails()->getTotals();
        $result = true;
        $subtotal = 0;
        $i = 0;

        foreach ($products as $product) {
            $price = $product->getData('price') * $qtys[$i];
            $subtotal += $price;
            $i++;
        }
        preg_match('/\d+\.?\d+/', $totals['grand_total'], $match);
        $uiPrice = array_shift($match);
        if (0.001 < ($uiPrice - ($subtotal + $subtotal * $tax / 100))) {
            $result = false;
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Grand total is not correct.'
        );
    }

    /**
     * Check notification message
     *
     * @param $negotiableQuoteView
     */
    protected function checkNotificationMessage(NegotiableQuoteView $negotiableQuoteView)
    {
        $notificationMessage = 'The discount for this quote has been removed because you have already used ' .
            'your discount on previous orders. Please re-submit the quote to the Seller for further negotiation.';

        \PHPUnit\Framework\Assert::assertEquals(
            $notificationMessage,
            $negotiableQuoteView->getQuoteDetails()->getNotificationMessage(),
            'Quote lock is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote data is correct.';
    }
}
