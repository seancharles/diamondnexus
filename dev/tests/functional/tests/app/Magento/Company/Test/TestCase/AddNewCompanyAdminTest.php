<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Preconditions:
 * 1. Create customer with company.
 *
 * Steps:
 * 1. Login to the admin panel and add new company admin.
 * 2. Verify that new customer is created and set as company admin.
 *
 * @group Company
 * @ZephyrId MAGETWO-68249
 */
class AddNewCompanyAdminTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Company index page.
     *
     * @var CompanyIndex
     */
    protected $companyIndex;

    /**
     * Company edit page.
     *
     * @var CompanyEdit
     */
    protected $companyEdit;

    /**
     * Add new company admin.
     *
     * @param \Magento\Company\Test\Fixture\Company $company
     * @param Customer $customer
     * @return void
     */
    protected function addNewCompanyAdmin(
        \Magento\Company\Test\Fixture\Company $company,
        Customer $customer
    ) {
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $this->companyEdit->getCompanyForm()->changeCompanyAdmin($customer);
        $this->companyEdit->getFormPageActions()->save();
    }

    /**
     * @param FixtureFactory $fixtureFactory
     * @param ObjectManager $objectManager
     * @param \Magento\Company\Test\Page\Adminhtml\CompanyIndex $companyIndex
     * @param \Magento\Company\Test\Page\Adminhtml\CompanyEdit $companyEdit
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        ObjectManager $objectManager,
        \Magento\Company\Test\Page\Adminhtml\CompanyIndex $companyIndex,
        \Magento\Company\Test\Page\Adminhtml\CompanyEdit $companyEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Add new company admin in the AP.
     *
     * @param Customer $companyAdmin
     * @param Customer $customer
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        Customer $customer,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $companyAdmin->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $companyAdmin->getEmail(),
                ],
            ]
        );
        $company->persist();

        //Steps
        $this->addNewCompanyAdmin($company, $customer);

        return [
            'company' => $company,
            'customer' => $customer,
        ];
    }

    /**
     * Roll back config settings.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
