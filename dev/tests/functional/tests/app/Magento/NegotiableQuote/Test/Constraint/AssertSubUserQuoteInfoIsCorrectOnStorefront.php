<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Class AssertSubUserQuoteInfoIsCorrectOnStorefront
 */
class AssertSubUserQuoteInfoIsCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param Customer $subUser
     * @param array $subUserQuote
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        Customer $subUser,
        array $subUserQuote = []
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();
        $this->checkCreatedBy($subUser, $negotiableQuoteView);
        $this->checkName($subUserQuote, $negotiableQuoteView);
        $this->checkDisabledButtons($negotiableQuoteView);
        $this->checkMessage($negotiableQuoteView);
    }

    /**
     * Check created by block contains correct name
     *
     * @param Customer $subUser
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkCreatedBy(Customer $subUser, NegotiableQuoteView $negotiableQuoteView)
    {
        $createdByText = $negotiableQuoteView->getQuoteDetails()->getCreatedBy();
        $result = strpos($createdByText, $subUser->getFirstname()) && strpos($createdByText, $subUser->getLastname());

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Created By name is not correct.'
        );
    }

    /**
     * Check quote name is correct
     *
     * @param array $quote
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkName(array $quote, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $negotiableQuoteView->getQuoteDetails()->getQuoteName(),
            'Quote name is not correct.'
        );
    }

    /**
     * Check disabled buttons
     *
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkDisabledButtons(NegotiableQuoteView $negotiableQuoteView)
    {
        $disabledButtonsFront = ['checkout', 'delete', 'send'];
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteView->getQuoteDetails()->areButtonsDisabled($disabledButtonsFront),
            'Disabled buttons are not correct.'
        );
    }

    /**
     * Check messages
     *
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkMessage(NegotiableQuoteView $negotiableQuoteView)
    {
        $message = 'You are not an owner of this quote. You cannot edit it or take any actions on it.';
        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $negotiableQuoteView->getMessagesBlock()->getNoticeMessage(),
            'Decline message is not correct.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Company sub user quote info is correct';
    }
}
