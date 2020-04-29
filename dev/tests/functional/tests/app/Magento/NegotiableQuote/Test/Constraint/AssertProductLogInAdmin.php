<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Assert that product updates are logged in the history log in Admin.
 *
 * Class AssertProductLogInAdmin
 */
class AssertProductLogInAdmin extends AbstractConstraint
{
    /**
     * Assert that product updates are logged in the history log in Admin.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param array $quote
     * @param array $historyLog
     */
    public function processAssert(
        NegotiableQuoteEdit $negotiableQuoteEdit,
        NegotiableQuoteIndex $negotiableQuoteGrid,
        array $quote,
        array $historyLog
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);

        $this->checkHistoryLog($historyLog, $negotiableQuoteEdit);
    }

    /**
     * Check history log
     *
     * @param array $historyLog
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkHistoryLog(array $historyLog, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $result = true;
        $negotiableQuoteEdit->getQuoteDetails()->openHistoryLogTab();
        $productsAddedLog = $negotiableQuoteEdit->getQuoteDetails()->getAddedProductsLog();
        $productsUpdatedLog = $negotiableQuoteEdit->getQuoteDetails()->getUpdatedProductsLog();

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
