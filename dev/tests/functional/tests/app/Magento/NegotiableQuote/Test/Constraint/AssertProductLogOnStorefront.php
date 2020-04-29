<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Assert that product updates are logged in the history log on Storefront
 *
 * Class AssertProductLogOnStorefront
 */
class AssertProductLogOnStorefront extends AbstractConstraint
{
    /**
     * Assert that product updates are logged in the history log on Storefront
     *
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param array $historyLog
     */
    public function processAssert(
        NegotiableQuoteView $negotiableQuoteView,
        NegotiableQuoteGrid $negotiableQuoteGrid,
        array $historyLog
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();

        $this->checkHistoryLog($historyLog, $negotiableQuoteView);
    }

    /**
     * Check history log
     *
     * @param array $historyLog
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function checkHistoryLog(array $historyLog, NegotiableQuoteView $negotiableQuoteView)
    {
        $result = true;
        $negotiableQuoteView->getQuoteDetails()->openHistoryLogTab();
        $productsAddedLog = $negotiableQuoteView->getQuoteDetails()->getAddedProductsLog();
        $productsUpdatedLog = $negotiableQuoteView->getQuoteDetails()->getUpdatedProductsLog();

        foreach ($productsUpdatedLog as $productUpdatedLog) {
            if (strpos($productUpdatedLog, $historyLog['updatedProductName']) === false) {
                $result = false;
                break;
            }
        }
        foreach ($productsAddedLog as $productAddedLog) {
            if (strpos($productAddedLog, $historyLog['addedProductName']) === false) {
                $result = false;
                break;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'History log is not correct'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'History log is correct.';
    }
}
