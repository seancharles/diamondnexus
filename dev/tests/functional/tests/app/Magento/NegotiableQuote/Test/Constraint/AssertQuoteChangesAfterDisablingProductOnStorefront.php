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
 * Class AssertQuoteChangesAfterDisablingProductOnStorefront
 */
class AssertQuoteChangesAfterDisablingProductOnStorefront extends AbstractConstraint
{
    /**
     * @var string
     */
    private $taxLabel = 'Estimated Tax';

    /**
     * Process assert.
     *
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendEdit
     * @param string $sku
     */
    public function processAssert(
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendEdit,
        $sku
    ) {
        $this->openQuoteEditPage($quoteFrontendGrid);
        $this->assertProductDeletedMessage($quoteFrontendEdit, $sku);
        $this->assertQuoteTotalsTaxLabel($quoteFrontendEdit);
        $this->assertQuoteTotalsSubtotalTaxLabel($quoteFrontendEdit);
        $this->assertHistoryMessage($quoteFrontendEdit, $sku);
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
     * Open quote edit page.
     *
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     */
    private function openQuoteEditPage(NegotiableQuoteGrid $quoteFrontendGrid)
    {
        $quoteFrontendGrid->open();
        $quoteFrontendGrid->getQuoteGrid()->openFirstItem();
    }

    /**
     * Assert product deleted message.
     *
     * @param NegotiableQuoteView $quoteFrontendEdit
     * @param string $sku
     */
    private function assertProductDeletedMessage(NegotiableQuoteView $quoteFrontendEdit, $sku)
    {
        $expectedNoticeMessage = 'Product ' . $sku . ' is no longer available. It was removed from your quote.';
        $noticeMessage = $quoteFrontendEdit->getMessagesBlock()->getNoticeMessage();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedNoticeMessage,
            $noticeMessage,
            'Notice message is incorrect'
        );
    }

    /**
     * Assert quote totals tax label.
     *
     * @param NegotiableQuoteView $quoteFrontendEdit
     */
    private function assertQuoteTotalsTaxLabel(NegotiableQuoteView $quoteFrontendEdit)
    {
        $quoteTotalsLabel = $quoteFrontendEdit->getQuoteDetails()->getQuoteTotalsTaxLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->taxLabel,
            $quoteTotalsLabel,
            'Notice message is incorrect'
        );
    }

    /**
     * Assert quote subtotal tax label.
     *
     * @param NegotiableQuoteView $quoteFrontendEdit
     */
    private function assertQuoteTotalsSubtotalTaxLabel(NegotiableQuoteView $quoteFrontendEdit)
    {
        $quoteTotalsSubtotalLabel = $quoteFrontendEdit->getQuoteDetails()->getQuoteTotalsTaxLabel();

        \PHPUnit\Framework\Assert::assertEquals(
            $this->taxLabel,
            $quoteTotalsSubtotalLabel,
            'Notice message is incorrect'
        );
    }

    /**
     * Assert quote history messages.
     *
     * @param NegotiableQuoteView $quoteFrontendEdit
     * @param string $sku
     */
    private function assertHistoryMessage(NegotiableQuoteView $quoteFrontendEdit, $sku)
    {
        $expectedHistoryLogMessage = $sku . ' - deleted from catalog';
        $quoteFrontendEdit->getQuoteDetails()->openHistoryLogTab();
        $historyMessages = $quoteFrontendEdit->getQuoteDetails()->getHistoryLog();

        \PHPUnit\Framework\Assert::assertTrue(
            in_array($expectedHistoryLogMessage, $historyMessages),
            'Notice message is incorrect'
        );
    }
}
