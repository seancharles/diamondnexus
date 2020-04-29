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
 * Class AssertAdminQuoteInfoIsCorrectOnStorefront
 */
class AssertAdminQuoteInfoIsCorrectOnStorefront extends AbstractConstraint
{
    /**
     * Grid loader selector.
     *
     * @var string
     */
    private $loader = '[data-role="spinner"]';

    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param Customer $admin
     * @param array $adminQuote
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        Customer $admin,
        array $adminQuote = []
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->waitForElementNotVisible($this->loader);
        $negotiableQuoteGrid->getQuoteGrid()->clickShowMyQuotesButton();
        $negotiableQuoteGrid->getQuoteGrid()->openItem($adminQuote);
        $this->checkCreatedBy($admin, $negotiableQuoteView);
        $this->checkName($adminQuote, $negotiableQuoteView);
    }

    /**
     * Check created by block contains correct name
     *
     * @param Customer $admin
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkCreatedBy(Customer $admin, NegotiableQuoteView $negotiableQuoteView)
    {
        $createdByText = $negotiableQuoteView->getQuoteDetails()->getCreatedBy();
        $result = strpos($createdByText, $admin->getFirstname()) && strpos($createdByText, $admin->getLastname());

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
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Company admin quote info is correct';
    }
}
