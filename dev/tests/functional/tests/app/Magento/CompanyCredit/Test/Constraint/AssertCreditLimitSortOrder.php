<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCreditLimitSortOrder.
 */
class AssertCreditLimitSortOrder extends AbstractConstraint
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
    protected $creditLimitColumnHeaderLabel = 'Credit Limit';

    /**
     * Id column header label.
     *
     * @var string
     */
    protected $idColumnHeaderLabel = 'ID';

    /**
     * Assert that sort order is correct after sorting companies by credit limit.
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
        $this->companyIndex->getGrid()->addColumnByName($this->creditLimitColumnHeaderLabel);
        $this->sortByEntityId();
        $this->sortByCreditLimit();
        $creditLimitsList = $this->getCreditLimitsList();
        $sortedCreditLimitsList = $this->getSortedCreditLimitsList($creditLimitsList);
        $this->verifyCreditLimitsSortOrder($sortedCreditLimitsList, $creditLimitsList);
        $this->companyIndex->open();
        $this->sortByCreditLimit();
        $creditLimitsList = $this->getCreditLimitsList();
        $reverseSortedCreditLimitsList = $this
            ->getReverseSortedOutstandingBalancesList($creditLimitsList);
        $this->verifyCreditLimitsSortOrder($reverseSortedCreditLimitsList, $creditLimitsList);
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
     * Sort by credit limit.
     *
     * @return void
     */
    private function sortByCreditLimit()
    {
        $this->companyIndex->getGrid()->sortByColumn($this->creditLimitColumnHeaderLabel);
    }

    /**
     * Get credit limits list.
     *
     * @return array
     */
    private function getCreditLimitsList()
    {
        $creditLimit = [];
        $ids = $this->companyIndex->getGrid()->getAllIds();

        foreach ($ids as $id) {
            $creditLimit[] = (float)preg_replace(
                "/[^\-\.0-9]/",
                "",
                $this->companyIndex->getGrid()->getColumnValue($id, $this->creditLimitColumnHeaderLabel)
            );
        }

        return $creditLimit;
    }

    /**
     * Get sorted credit limits list.
     *
     * @param array $namesList
     * @return array
     */
    private function getSortedCreditLimitsList(array $namesList)
    {
        asort($namesList);
        return $namesList;
    }

    /**
     * Get reverse sorted credit limits list.
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
     * Verify credit limit sort order.
     *
     * @param array $expectedNamesList
     * @param array $namesList
     * @return void
     */
    private function verifyCreditLimitsSortOrder(array $expectedNamesList, array $namesList)
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
