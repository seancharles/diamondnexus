<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;

/**
 * Assert that company has correct customer group on company grid.
 */
class AssertCompanyCustomerGroupCorrect extends AbstractConstraint
{
    /**
     * @var string
     */
    private $groupColumnName;

    /**
     * Assert that company has correct customer group on company grid.
     *
     * @param CompanyIndex $companyIndex
     * @param string $customerGroup
     * @param string $companyName
     * @param string $columnName [optional]
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        $customerGroup,
        $companyName,
        $columnName = 'Customer Group'
    ) {
        $this->groupColumnName = $columnName;
        $companyIndex->open();
        $companyIndex->getGrid()->search(['customer_group_id' => $customerGroup, 'company_name' => $companyName]);
        $rowId = $companyIndex->getGrid()->getFirstItemId();

        \PHPUnit\Framework\Assert::assertEquals(
            $customerGroup,
            $companyIndex->getGrid()->getColumnValue($rowId, $columnName),
            $this->groupColumnName . ' is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return $this->groupColumnName . ' is correct.';
    }
}
