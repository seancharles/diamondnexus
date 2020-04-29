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
 * Class AssertQuoteChangesAfterDisablingProductInAdmin
 */
class AssertQuoteChangesAfterDisablingProductInAdmin extends AbstractConstraint
{
    /**
     * @var string
     */
    private $taxLabel = 'Estimated Tax';

    /**
     * @var string
     */
    private $discountTypePercentage = 'percentage';

    /**
     * @var string
     */
    private $percentageDiscountValue = '10';

    /**
     * Process assert.
     *
     * @param array $quote
     * @param NegotiableQuoteIndex $negotiableQuoteIndex
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $sku
     */
    public function processAssert(
        array $quote,
        NegotiableQuoteIndex $negotiableQuoteIndex,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        $sku
    ) {
        $negotiableQuoteIndex->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteIndex->getGrid()->searchAndOpen($filter);
        $this->assertProductDeletedMessage($negotiableQuoteEdit, $sku);
        $negotiableQuoteEdit->getQuoteDetails()->openHistoryLogTab();
        $this->assertHistoryLogMessage($negotiableQuoteEdit, $sku);
        $this->assertQuoteItemsTaxLabel($negotiableQuoteEdit);
        $this->assertQuoteTotalsTaxLabels($negotiableQuoteEdit);
        $this->assertQuoteTotalsSubtotalTaxLabels($negotiableQuoteEdit);
        $this->setPercentageDiscount($negotiableQuoteEdit);
        $this->sendQuote($negotiableQuoteEdit);
    }

    /**
     * toString.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote changes are correct.';
    }

    /**
     * Assert product deleted message.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $sku
     */
    private function assertProductDeletedMessage(NegotiableQuoteEdit $negotiableQuoteEdit, $sku)
    {
        $expectedWarningMessage = 'Product ' . $sku
            . ' has been deleted from the catalog. The items quoted list has been updated.';
        $warningMessages = $negotiableQuoteEdit->getQuoteMessages()->getWarningMessages();

        \PHPUnit\Framework\Assert::assertTrue(
            in_array($expectedWarningMessage, $warningMessages),
            'Product deleted message is incorrect'
        );
    }

    /**
     * Assert history log message.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param string $sku
     */
    private function assertHistoryLogMessage(NegotiableQuoteEdit $negotiableQuoteEdit, $sku)
    {
        $expectedHistoryMessage = $sku . ' - deleted from catalog';
        $historyLogMessages = $negotiableQuoteEdit->getQuoteDetails()->getHistoryLog();

        \PHPUnit\Framework\Assert::assertTrue(
            in_array($expectedHistoryMessage, $historyLogMessages),
            'History log message is incorrect'
        );
    }

    /**
     * Assert quote items tax label.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function assertQuoteItemsTaxLabel(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $quoteItemTaxLabel = $negotiableQuoteEdit->getQuoteDetails()->getQuoteItemsTaxLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->taxLabel,
            $quoteItemTaxLabel,
            'Quote items subtotal tax label is incorrect'
        );
    }

    /**
     * Assert quote totals tax label.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function assertQuoteTotalsTaxLabels(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $quoteTotalsTaxLabel = $negotiableQuoteEdit->getQuoteDetails()->getQuoteTotalsTaxLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->taxLabel,
            $quoteTotalsTaxLabel,
            'Quote totals tax label is incorrect'
        );
    }

    /**
     * Assert quote totals subtotal tax label.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function assertQuoteTotalsSubtotalTaxLabels(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $quoteTotalsSubtotalTaxLabel = $negotiableQuoteEdit->getQuoteDetails()->getQuoteTotalsSubtotalTaxLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->taxLabel,
            $quoteTotalsSubtotalTaxLabel,
            'Quote totals subtotal tax label is incorrect'
        );
    }

    /**
     * Set percentage discount.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function setPercentageDiscount(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $negotiableQuoteEdit->getQuoteDetails()
            ->fillDiscount($this->discountTypePercentage, $this->percentageDiscountValue);
    }

    /**
     * Send quote.
     *
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    private function sendQuote(NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $negotiableQuoteEdit->getQuoteDetailsActions()->send();
    }
}
