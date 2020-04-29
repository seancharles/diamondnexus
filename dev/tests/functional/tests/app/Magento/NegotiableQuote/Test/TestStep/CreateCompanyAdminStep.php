<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;

/**
 * Create Company with company admin.
 */
class CreateCompanyAdminStep implements TestStepInterface
{
    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var string
     */
    private $company;

    /**
     * Logout customer on frontend step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontend;

    /**
     * @param LogoutCustomerOnFrontendStep $logout
     * @param FixtureFactory $fixtureFactory
     * @param Customer $customer
     * @param string $company
     */
    public function __construct(
        LogoutCustomerOnFrontendStep $logout,
        FixtureFactory $fixtureFactory,
        Customer $customer,
        $company
    ) {
        $this->logoutCustomerOnFrontend = $logout;
        $this->fixtureFactory = $fixtureFactory;
        $this->customer = $customer;
        $this->company = $company;
    }

    /**
     * Create Company and return company admin.
     *
     * @return array
     */
    public function run()
    {
        $this->customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => $this->company,
                'data' => [
                    'email' => $this->customer->getEmail(),
                ],
            ]
        );

        $company->persist();

        return ['customer' => $this->customer, 'company' => $company];
    }

    /**
     * Logout customer on fronted.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->logoutCustomerOnFrontend->run();
    }
}
