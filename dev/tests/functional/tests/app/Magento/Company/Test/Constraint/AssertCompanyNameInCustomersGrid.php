<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company;

/**
 * Class AssertCompanyNameInCustomersGrid.
 */
class AssertCompanyNameInCustomersGrid extends AbstractConstraint
{
    /**
     * Customer edit.
     *
     * @var CustomerIndexEdit
     */
    protected $customerEdit;

    /**
     * Company customer index.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Column header label.
     *
     * @var string
     */
    protected $columnHeaderLabel = 'Company';

    /**
     * Assert that company info is correct in customer grid.
     *
     * @param Customer $customer
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param CustomerIndexEdit $customerEdit
     * @param CustomerIndex $customerIndex
     * @param Company $company
     * @return void
     */
    public function processAssert(
        Customer $customer,
        Customer $companyAdmin,
        Customer $companyUser,
        CustomerIndexEdit $customerEdit,
        CustomerIndex $customerIndex,
        Company $company
    ) {
        $this->customerEdit = $customerEdit;
        $this->customerIndex = $customerIndex;
        $this->customerIndex->open();
        $this->isColumnVisible();
        $this->isCompanyFilterVisible();
        $this->verifyCustomerCompany($customer, '');
        $this->verifyCustomerCompany($companyAdmin, $company->getCompanyName());
        $this->verifyCustomerCompany($companyUser, $company->getCompanyName());
        $this->verifyCompanyAfterFilteringByCompanyName($company->getCompanyName());
    }

    /**
     * Check customer company name on customer grid.
     *
     * @param Customer $customer
     * @param string $expectedCompanyName
     * @return void
     */
    private function verifyCustomerCompany(Customer $customer, $expectedCompanyName)
    {
        $filter = $this->getCustomerFilter($customer);
        $this->customerIndex->getCustomerGridBlock()->search($filter);
        $itemId = $this->customerIndex->getCustomerGridBlock()->getFirstItemId();
        $companyName = $this->customerIndex->getCompanyCustomerGrid()
            ->getColumnValue($itemId, $this->columnHeaderLabel);

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedCompanyName,
            $companyName,
            'Company name is incorrect.'
        );
    }

    /**
     * Check if company column is visible in grid.
     *
     * @return void
     */
    private function isColumnVisible()
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $this->customerIndex->getCompanyCustomerGrid()->isColumnVisible($this->columnHeaderLabel),
            'The column' . $this->columnHeaderLabel . ' is not visible in grid.'
        );
    }

    /**
     * Check if company filter is visible.
     *
     * @return void
     */
    private function isCompanyFilterVisible()
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $this->customerIndex->getCompanyCustomerGrid()->isCompanyFilterVisible(),
            'Company filter is not visible in grid.'
        );
    }

    /**
     * Verify company after filtering by company name.
     *
     * @param string $expectedCompanyName
     * @return void
     */
    private function verifyCompanyAfterFilteringByCompanyName($expectedCompanyName)
    {
        $filter = $this->getCustomerCompanyFilter($expectedCompanyName);
        $this->customerIndex->getCompanyCustomerGrid()->search($filter);
        $itemIds = $this->customerIndex->getCustomerGridBlock()->getAllIds();

        foreach ($itemIds as $itemId) {
            $companyName = $this->customerIndex->getCompanyCustomerGrid()
                ->getColumnValue($itemId, $this->columnHeaderLabel);

            \PHPUnit\Framework\Assert::assertEquals(
                $expectedCompanyName,
                $companyName,
                'Company name is incorrect.'
            );
        }
    }

    /**
     * Get customer filter.
     *
     * @param Customer $customer
     * @return array
     */
    private function getCustomerFilter(Customer $customer)
    {
        return ['email' => $customer->getEmail()];
    }

    /**
     * Get customer company filter.
     *
     * @param string $companyName
     * @return array
     */
    private function getCustomerCompanyFilter($companyName)
    {
        return ['company_name' => $companyName];
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All customer info is correct.';
    }
}
