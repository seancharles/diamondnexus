<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Assert error message is displayed.
 */
class AssertCustomerNotLoginErrorMessage extends AbstractConstraint
{
    /**
     * Assert error message is displayed.
     *
     * @param CustomerAccountLogin $customerAccountLogin
     * @param Customer $customer
     * @return void
     */
    public function processAssert(CustomerAccountLogin $customerAccountLogin, Customer $customer)
    {
        $customerAccountLogin->open();
        $customerAccountLogin->getLoginBlock()->fill($customer);
        $customerAccountLogin->getLoginBlock()->submit();

        \PHPUnit\Framework\Assert::assertTrue(
            $customerAccountLogin->getMessages()->assertErrorMessage(),
            'Error message is not displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Error message is displayed.";
    }
}
