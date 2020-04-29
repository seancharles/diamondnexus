<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Company\Test\Fixture\Company;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\CompanyAccount;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Enable Company module.
 *
 * Steps:
 * 1. Go to the Company Account creation page.
 * 2. Fill in the form.
 * 3. Submit form.
 * 4. Verify that user is assigned to a correct company and has correct user group.
 *
 * @group Company
 * @ZephyrId MAGETWO-68226, @ZephyrId MAGETWO-67929
 */
class CreateCompanyAccountTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var CompanyAccount
     */
    private $companyAccount;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Perform needed injections.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyAccount $companyAccount
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyAccount $companyAccount,
        TestStepFactory $stepFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyAccount = $companyAccount;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Create company entity from Storefront
     *
     * @param Company $company
     * @param string $configData [optional]
     * @return array
     */
    public function test(Company $company, $configData = null)
    {
        //Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        //Steps
        $this->companyAccount->open();
        $this->companyAccount->getCompanyAccount()->fill($company);
        $this->companyAccount->getCompanyAccount()->submit();
        $customer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default',
                'data' => ['email' => $company->getEmail()]
            ]
        );

        return [
            'customersCompany' => [$customer],
            'customer' => $customer,
            'companyName' => $company->getCompanyName()
        ];
    }

    /**
     * Roll back config settings
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
