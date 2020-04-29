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
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Preconditions:
 * 1. Create customer with company.
 *
 * Steps:
 * 1. Login to Admin panel.
 * 2. Open OldAdmin customer details page.
 * 3. Click Delete Customer btn.
 *
 * @group Company
 * @ZephyrId MAGETWO-68248
 */
class DeleteCompanyAdminTest extends Injectable
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
     * Customers grid.
     *
     * @var CustomerIndex $customerIndex
     */
    private $customerIndex;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit $customerIndexEdit
     */
    private $customerIndexEdit;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param ObjectManager $objectManager
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        ObjectManager $objectManager,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
    }

    /**
     * Delete Company Admin user.
     *
     * @param Customer $companyAdmin
     * @param string|null $configData
     * @return array
     */
    public function test(
        Customer $companyAdmin,
        $configData = null
    ) {
        //Preconditions:
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

        // Steps:
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $companyAdmin->getEmail()]);
        $this->customerIndexEdit->getPageActionsBlock()->delete();
        $this->customerIndexEdit->getModalBlock()->acceptAlert();
    }
}
