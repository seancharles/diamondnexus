<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;

/**
 * Assert that autocomplete on "Enter SKU or Product Name" field is correct.
 */
class AssertQuickOrderAutocomplete extends AbstractConstraint
{
    /**
     * Assert that autocomplete on "Enter SKU or Product Name" field is correct.
     *
     * @param QuickOrderPage $quickOrderPage
     * @return void
     */
    public function processAssert(
        QuickOrderPage $quickOrderPage
    ) {
        $itemBlock = $quickOrderPage->getItems()->getItemBlock();
        $list = $itemBlock->getAutocompleteResultList();
        $sortedList = $this->retrieveSortedTitlesList($list);

        \PHPUnit\Framework\Assert::assertEquals(
            $list,
            $sortedList,
            'Sort order is not correct.'
        );
        $quickOrderPage->getItems()->selectFirstItem();
        $quickOrderPage->getItems()->focusOutFromInput();
        \PHPUnit\Framework\Assert::assertTrue(
            $itemBlock->isResultVisible(),
            'Result block is not visible.'
        );
    }

    /**
     * Retrieve sorted list.
     *
     * @param array $titlesList
     * @return array
     */
    private function retrieveSortedTitlesList(array $titlesList)
    {
        asort($titlesList);
        return $titlesList;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Autocomplete on "Enter SKU or Product Name" field is correct.';
    }
}
