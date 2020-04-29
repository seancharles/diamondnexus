<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

/**
 * Assert that Credit History data is correct on Storefront.
 */
class AssertCreditHistoryIsCorrectOnStorefront extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Process assert.
     *
     * @param \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory
     * @param string|null $historyDataSet
     * @param array $amounts
     * @param string|null $poNumber
     * @return void
     */
    public function processAssert(
        \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory,
        $historyDataSet = null,
        array $amounts = [],
        $poNumber = null
    ) {
        $creditHistory->open();
        $this->checkBalance($amounts, $creditHistory);
        if (!empty($historyDataSet)) {
            $this->checkHistoryGrid($this->getExpectedDataSet($historyDataSet, $amounts, $poNumber), $creditHistory);
        }
    }

    /**
     * Check balance.
     *
     * @param array $amounts
     * @param \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory
     * @return void
     */
    public function checkBalance(array $amounts, \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            (float)$amounts['outstandingBalance'],
            $creditHistory->getCreditHistory()->getOutstandingBalance(),
            'Outstanding Balance value is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            (float)$amounts['availableCredit'],
            $creditHistory->getCreditHistory()->getAvailableCredit(),
            'Available Credit value is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            (float)$amounts['creditLimit'],
            $creditHistory->getCreditHistory()->getCreditLimit(),
            'Credit Limit value is incorrect.'
        );
    }

    /**
     * Check History Grid
     *
     * @param array $expectedDataSet
     * @param \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory
     * @return void
     */
    public function checkHistoryGrid(
        array $expectedDataSet,
        \Magento\CompanyCredit\Test\Page\CreditHistory $creditHistory
    ) {
        $creditData = $creditHistory->getCreditHistory()->getHistory();
        $result = true;
        $diffLog = [];
        foreach ($expectedDataSet as $key => $operationData) {
            if (count(array_intersect($operationData, $creditData[$key])) !== count($operationData)) {
                $result = false;
                $diffLog[] = 'Expected: ' . implode(', ', $operationData);
                $diffLog[] = 'Actual: ' . implode(', ', explode("\n", $creditData[$key]));
                break;
            }
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Credit History grid on Credit History page is incorrect.' . "\n" . implode("\n", $diffLog)
        );
    }

    /**
     * Get expected data set of operations in credit history grid.
     *
     * @param string $setId
     * @param array $amounts
     * @param string $poNumber
     * @return array
     */
    private function getExpectedDataSet($setId, array $amounts, $poNumber)
    {
        $operation = $setId == 'reimburse' ? 'Reimbursed' : 'Refunded';
        $dataSet = [
            $operation => [
                $operation,
                $this->formatAmount($amounts[$setId]),
                $this->formatAmount($amounts['outstandingBalance']),
                $this->formatAmount($amounts['availableCredit']),
                $this->formatAmount($amounts['creditLimit']),
            ],
            'Purchased' => [
                'Purchased',
                $this->formatAmount($amounts['outstandingBalance'] - $amounts[$setId]),
                $this->formatAmount(
                    $amounts['creditLimit'] + $amounts['outstandingBalance'] - $amounts[$setId]
                ),
                $this->formatAmount($amounts['creditLimit']),
                $poNumber
            ],
            'Allocated' => [
                'Allocated',
                $this->formatAmount($amounts['creditLimit']),
            ],
        ];
        return $dataSet;
    }

    /**
     * Get amount with dollar sign.
     *
     * @param int|float|string $amount
     * @return string
     */
    private function formatAmount($amount)
    {
        return ((float)$amount < 0 ? '-' : '') . '$' . sprintf("%.2f", abs((float)$amount));
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Matched credit history is correct.';
    }
}
