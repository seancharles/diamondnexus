<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that sort order is correct after sorting orders by company name.
 */
class AssertOrdersGridSortOrder extends AbstractConstraint
{
    /**
     * Company Name column header label.
     *
     * @var string
     */
    private $companyNameColumnHeaderLabel = 'Company Name';

    /**
     * Orders grid page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Assert that sort order is correct after sorting orders by company name.
     *
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(
        OrderIndex $orderIndex
    ) {
        $this->orderIndex = $orderIndex;
        $ordersTitles = $this->getOrderTitleList();
        $this->orderIndex->getSalesOrderGrid()->sortByColumn($this->companyNameColumnHeaderLabel);
        $sortedOrdersTitles = $this->retrieveSortedTitlesList($ordersTitles);
        \PHPUnit\Framework\Assert::assertEquals(
            $ordersTitles,
            $sortedOrdersTitles,
            'Sort order is not correct.'
        );
    }

    /**
     * Get order titles list.
     *
     * @return array
     */
    private function getOrderTitleList()
    {
        $ordersTitles = [];
        $ids = $this->orderIndex->getSalesOrderGrid()->getAllIds();

        foreach ($ids as $id) {
            $ordersTitles[] = $this->orderIndex->getSalesOrderGrid()
                ->getColumnValue($id, $this->companyNameColumnHeaderLabel);
        }
        return $ordersTitles;
    }

    /**
     * Retrieve sorted titles list.
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
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sort order is correct.';
    }
}
