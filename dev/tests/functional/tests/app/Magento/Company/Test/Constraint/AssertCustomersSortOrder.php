<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Class AssertCustomersSortOrder.
 */
class AssertCustomersSortOrder extends AbstractConstraint
{
    /**
     * Company customer index.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Company column header label.
     *
     * @var string
     */
    protected $companyColumnHeaderLabel = 'Company';

    /**
     * Id column header label.
     *
     * @var string
     */
    protected $idColumnHeaderLabel = 'ID';

    /**
     * Assert that sort order is correct after sorting customers by company name.
     *
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(
        CustomerIndex $customerIndex
    ) {
        $this->customerIndex = $customerIndex;
        $this->customerIndex->open();
        $this->customerIndex->getCompanyCustomerGrid()->resetFilter();
        $this->customerIndex->getCustomerGridBlock()->resetFilter();
        $this->sortByEntityId();
        $this->sortByCompanyName();
        $namesList = $this->getCompanyNamesList();
        $sortedNamesList = $this->getSortedNamesList($namesList);
        $this->verifyCompanyNamesSortOrder($sortedNamesList, $namesList);
        $this->customerIndex->open();
        $this->sortByCompanyName();
        $namesList = $this->getCompanyNamesList();
        $reverseSortedNamesList = $this->getReverseSortedNamesList($namesList);
        $this->verifyCompanyNamesSortOrder($reverseSortedNamesList, $namesList);
    }

    /**
     * Filter by entity id.
     *
     * @return void
     */
    private function sortByEntityId()
    {
        $this->customerIndex->getCompanyCustomerGrid()->sortByColumn($this->idColumnHeaderLabel);
    }

    /**
     * Filter by company name.
     *
     * @return void
     */
    private function sortByCompanyName()
    {
        $this->customerIndex->getCompanyCustomerGrid()->sortByColumn($this->companyColumnHeaderLabel);
    }

    /**
     * Get company names list.
     *
     * @return array
     */
    private function getCompanyNamesList()
    {
        $companyNames = [];
        $ids = $this->customerIndex->getCompanyCustomerGrid()->getAllIds();

        foreach ($ids as $id) {
            $companyNames[] = $this->customerIndex->getCompanyCustomerGrid()
                ->getColumnValue($id, $this->companyColumnHeaderLabel);
        }

        return $companyNames;
    }

    /**
     * Get sorted names list.
     *
     * @param array $namesList
     * @return array
     */
    private function getSortedNamesList(array $namesList)
    {
        asort($namesList);
        return $namesList;
    }

    /**
     * Get reverse sorted names list.
     *
     * @param array $namesList
     * @return array
     */
    private function getReverseSortedNamesList(array $namesList)
    {
        arsort($namesList);
        return $namesList;
    }

    /**
     * Verify company names sort order.
     *
     * @param array $expectedNamesList
     * @param array $namesList
     * @return void
     */
    private function verifyCompanyNamesSortOrder(array $expectedNamesList, array $namesList)
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
