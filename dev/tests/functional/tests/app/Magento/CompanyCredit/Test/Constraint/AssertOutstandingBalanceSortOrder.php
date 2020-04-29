<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOutstandingBalanceSortOrder.
 */
class AssertOutstandingBalanceSortOrder extends AbstractConstraint
{
    /**
     * Company index.
     *
     * @var CompanyIndex
     */
    protected $companyIndex;

    /**
     * Outstanding Balance column header label.
     *
     * @var string
     */
    protected $outstandingBalanceColumnHeaderLabel = 'Outstanding Balance';

    /**
     * Id column header label.
     *
     * @var string
     */
    protected $idColumnHeaderLabel = 'ID';

    /**
     * Assert that sort order is correct after sorting companies by outstanding balance.
     *
     * @param CompanyIndex $companyIndex
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex
    ) {
        $this->companyIndex = $companyIndex;
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->resetFilter();
        $this->sortByEntityId();
        $this->sortByOutstandingBalance();
        $outstandingBalancesList = $this->getOutstandingBalancesList();
        $sortedOutstandingBalancesList = $this->getSortedOutstandingBalancesList($outstandingBalancesList);
        $this->verifyOutstandingBalanceSortOrder($sortedOutstandingBalancesList, $outstandingBalancesList);
        $this->companyIndex->open();
        $this->sortByOutstandingBalance();
        $outstandingBalancesList = $this->getOutstandingBalancesList();
        $reverseSortedOutstandingBalancesList = $this
            ->getReverseSortedOutstandingBalancesList($outstandingBalancesList);
        $this->verifyOutstandingBalanceSortOrder($reverseSortedOutstandingBalancesList, $outstandingBalancesList);
    }

    /**
     * Sort by entity id.
     *
     * @return void
     */
    private function sortByEntityId()
    {
        $this->companyIndex->getGrid()->sortByColumn($this->idColumnHeaderLabel);
    }

    /**
     * Sort by outstanding balance.
     *
     * @return void
     */
    private function sortByOutstandingBalance()
    {
        $this->companyIndex->getGrid()->sortByColumn($this->outstandingBalanceColumnHeaderLabel);
    }

    /**
     * Get outstanding balances list.
     *
     * @return array
     */
    private function getOutstandingBalancesList()
    {
        $outstandingBalance = [];
        $ids = $this->companyIndex->getGrid()->getAllIds();

        foreach ($ids as $id) {
            $outstandingBalance[] = (float)preg_replace(
                "/[^\-\.0-9]/",
                "",
                $this->companyIndex->getGrid()->getColumnValue($id, $this->outstandingBalanceColumnHeaderLabel)
            );
        }

        return $outstandingBalance;
    }

    /**
     * Get sorted outstanding balances list.
     *
     * @param array $namesList
     * @return array
     */
    private function getSortedOutstandingBalancesList(array $namesList)
    {
        asort($namesList);
        return $namesList;
    }

    /**
     * Get reverse sorted outstanding balances list.
     *
     * @param array $namesList
     * @return array
     */
    private function getReverseSortedOutstandingBalancesList(array $namesList)
    {
        arsort($namesList);
        return $namesList;
    }

    /**
     * Verify outstanding balance sort order.
     *
     * @param array $expectedNamesList
     * @param array $namesList
     * @return void
     */
    private function verifyOutstandingBalanceSortOrder(array $expectedNamesList, array $namesList)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedNamesList,
            $namesList,
            'Sort order is not correct.'
        );
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
