<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Company\Test\Page\CompanyAccount;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;

/**
 * Abstract test create entity on Storefront
 *
 */
abstract class AbstractCreateEntityStorefrontTest extends Injectable
{
    /**
     * Customer registry page
     *
     * @var CustomerAccountCreate
     */
    protected $customerAccountCreate;

    /**
     * Cms page
     *
     * @var CmsIndex $cmsIndex
     */
    protected $cmsIndex;

    /**
     * Company page
     *
     * @var CompanyPage $companyPage
     */
    protected $companyPage;

    /**
     * Company account
     *
     * @var CompanyAccount $companyAccount
     */
    protected $companyAccount;

    /**
     * Fixture factory
     *
     * @var FixtureFactory $fixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Customer log out step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontendStep;

    /**
     * CustomerIndex page
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Perform needed injections
     *
     * @param CustomerAccountCreate $customerAccountCreate
     * @param CmsIndex $cmsIndex
     * @param CompanyPage $companyPage
     * @param CompanyAccount $companyAccount
     * @param FixtureFactory $fixtureFactory
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep
     * @param CustomerIndex $customerIndex
     */
    public function __inject(
        CustomerAccountCreate $customerAccountCreate,
        CmsIndex $cmsIndex,
        CompanyPage $companyPage,
        CompanyAccount $companyAccount,
        FixtureFactory $fixtureFactory,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep,
        CustomerIndex $customerIndex
    ) {
        $this->customerAccountCreate = $customerAccountCreate;
        $this->cmsIndex = $cmsIndex;
        $this->companyPage = $companyPage;
        $this->companyAccount = $companyAccount;
        $this->fixtureFactory = $fixtureFactory;
        $this->logoutCustomerOnFrontendStep = $logoutCustomerOnFrontendStep;
        $this->customerIndex = $customerIndex;
    }

    /**
     * Create entity from Storefront
     *
     * @param Customer $customer
     * @param string $entity
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, $entity, $configData = null)
    {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        list($code, $dataset) = explode('/', $entity);
        $entity = $this->fixtureFactory->createByCode($code, ['dataset' => $dataset]);
        $addMethod = 'clickAdd' . ucfirst($code);
        $popupMethod = 'get' . ucfirst($code) . 'Popup';
        $this->customerAccountCreate->open();
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($customer);

        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_all_fields_and_sales_rep',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $company->persist();

        $this->companyPage->open();
        $this->companyPage->getTreeControl()->$addMethod();
        $this->companyPage->$popupMethod()->fill($entity);
        $this->companyPage->$popupMethod()->submit();

        return ['entity' => $entity, 'popupMethod' => $popupMethod];
    }

    /**
     * Reset config settings to default and logout customer.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->logoutCustomerOnFrontendStep->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
