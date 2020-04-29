<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Class AbstractCompanyTest.
 */
abstract class AbstractCompanyTest extends Injectable
{
    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    protected function logoutCustomer()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
    }

    /**
     * Gets method name.
     *
     * @param string $step
     * @return string
     */
    protected function getMethodName($step)
    {
        $step = explode('_', $step);
        $method = '';
        foreach ($step as $value) {
            if ($value) {
                $value = ucfirst($value);
            }
            $method .= $value;
        }

        return $method;
    }
}
