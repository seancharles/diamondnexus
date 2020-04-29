<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Assert that user is assigned to a correct company.
 */
class AssertUsersCompanyIsCorrect extends AbstractConstraint
{
    /**
     * Assert that user is assigned to a correct company.
     *
     * @param CustomerIndex $customerGrid
     * @param CustomerIndexEdit $customerView
     * @param array $customersCompany
     * @param string $companyName
     */
    public function processAssert(
        CustomerIndex $customerGrid,
        CustomerIndexEdit $customerView,
        array $customersCompany,
        $companyName
    ) {
        foreach ($customersCompany as $customer) {
            $filter = [
                'email' => $customer->getEmail(),
            ];
            $customerGrid->open();
            $customerGrid->getCustomerGridBlock()->searchAndOpen($filter);

            \PHPUnit\Framework\Assert::assertEquals(
                $companyName,
                $customerView->getCustomerView()->getCompanyName(),
                'User is assigned to an incorrect company.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'User is assigned to a correct company.';
    }
}
