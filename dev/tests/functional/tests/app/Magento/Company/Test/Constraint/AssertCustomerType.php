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

/**
 * Class AssertCustomerType.
 */
class AssertCustomerType extends AbstractConstraint
{
    /**
     * Customer edit.
     *
     * @var CustomerIndexEdit
     */
    private $customerEdit;

    /**
     * Company customer index.
     *
     * @var CustomerIndex
     */
    private $customerIndex;

    /**
     * Individual user.
     *
     * @var string
     */
    private $userTypeIndividualUser = 'Individual user';

    /**
     * Company admin.
     *
     * @var string
     */
    private $userTypeCompanyAdmin = 'Company admin';

    /**
     * Company user.
     *
     * @var string
     */
    private $userTypeCompanyUser = 'Company user';

    /**
     * Assert customer type on customer grid and on customer edit pages.
     *
     * @param Customer $customer
     * @param Customer $companyAdmin
     * @param Customer $companyUser
     * @param CustomerIndexEdit $customerEdit
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(
        Customer $customer,
        Customer $companyAdmin,
        Customer $companyUser,
        CustomerIndexEdit $customerEdit,
        CustomerIndex $customerIndex
    ) {
        $this->customerEdit = $customerEdit;
        $this->customerIndex = $customerIndex;
        $this->customerIndex->open();
        $this->assertCustomerTypeOnIndexPage($customer, $this->userTypeIndividualUser);
        $this->assertCustomerTypeOnIndexPage($companyAdmin, $this->userTypeCompanyAdmin);
        $this->assertCustomerTypeOnIndexPage($companyUser, $this->userTypeCompanyUser);
        $this->assertCustomerTypeOnEditPage($customer, $this->userTypeIndividualUser);
        $this->customerIndex->open();
        $this->assertCustomerTypeOnEditPage($companyAdmin, $this->userTypeCompanyAdmin);
        $this->customerIndex->open();
        $this->assertCustomerTypeOnEditPage($companyUser, $this->userTypeCompanyUser);
    }

    /**
     * toString.
     *
     * @return string
     */
    public function toString()
    {
        return 'User type is correct.';
    }

    /**
     * Assert customer type on index page.
     *
     * @param Customer $customer
     * @param string $expectedUserType
     * @return void
     */
    private function assertCustomerTypeOnIndexPage(Customer $customer, $expectedUserType)
    {
        $filter = $this->getCustomerFilter($customer);
        $this->customerIndex->getCustomerGridBlock()->search($filter);
        $itemId = $this->customerIndex->getCustomerGridBlock()->getFirstItemId();
        $userType = $this->customerIndex->getCompanyCustomerGrid()->getColumnValue($itemId, 'Customer Type');

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedUserType,
            $userType,
            'User type is incorrect.'
        );
    }

    /**
     * Assert customer type on edit page.
     *
     * @param Customer $customer
     * @param string $expectedUserType
     * @throws \Exception
     * @return void
     */
    private function assertCustomerTypeOnEditPage(Customer $customer, $expectedUserType)
    {
        $filter = $this->getCustomerFilter($customer);
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $userType = $this->customerEdit->getCustomerView()->getCustomerType();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedUserType,
            $userType,
            'User type is incorrect.'
        );
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
}
