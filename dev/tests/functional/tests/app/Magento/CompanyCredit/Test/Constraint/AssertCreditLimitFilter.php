<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCreditLimitFilter.
 */
class AssertCreditLimitFilter extends AbstractConstraint
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
     * Assert that correct companies are visible after applying sorting by "Credit Limit" field
     *
     * @param CompanyIndex $companyIndex
     * @param array $creditLimits
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        array $creditLimits
    ) {
        $filter = [
            'credit_limit_from' => $creditLimits[0],
            'credit_limit_to' => $creditLimits[1],
        ];
        $this->companyIndex = $companyIndex;
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->resetFilter();
        $this->companyIndex->getGrid()->addColumnByName($this->creditLimitColumnHeaderLabel);
        $creditLimitsList = $this->getCreditLimitsList($creditLimits[0], $creditLimits[1]);
        $this->companyIndex->getGrid()->search($filter);
        $creditLimitsListAfterFilter = $this->getCreditLimitsList();
        $this->verifyCreditLimitsSortOrder($creditLimitsListAfterFilter, $creditLimitsList);
    }

    /**
     * Get credit limits list.
     *
     * @param int|null $from
     * @param int|null $to
     * @return array
     */
    private function getCreditLimitsList($from = null, $to = null)
    {
        $creditLimits = [];
        $ids = $this->companyIndex->getGrid()->getAllIds();

        foreach ($ids as $id) {
            $creditLimit = (float)preg_replace(
                "/[^\-\.0-9]/",
                "",
                $this->companyIndex->getGrid()->getColumnValue($id, $this->creditLimitColumnHeaderLabel)
            );

            if ($from === null || $to === null || ($creditLimit >= $from && $creditLimit <= $to)) {
                $creditLimits[] = $creditLimit;
            }
        }

        return $creditLimits;
    }

    /**
     * Verify companies after applying filter.
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
            'Companies list is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Companies list is correct.';
    }
}
