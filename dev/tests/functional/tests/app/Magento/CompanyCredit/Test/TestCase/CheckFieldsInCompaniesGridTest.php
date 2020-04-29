<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Preconditions:
 * 1. Create 3 products.
 * 2. Create 3 companies.
 *
 * Steps:
 * 1. Set credit limit for each company.
 * 2. Place order with each company with payment on account method.
 * 3. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68349
 *
 * @SuppressWarnings(PHPMD)
 */
class CheckFieldsInCompaniesGridTest extends AbstractCompanyCreditTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Company index page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyIndex
     */
    private $companyIndex;

    /**
     * Company edit page.
     *
     * @var \Magento\Company\Test\Page\Adminhtml\CompanyEdit
     */
    private $companyEdit;

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Test new fields sorting and filtering in admin panel.
     *
     * @param array $customerDatasets
     * @param array $companyDatasets
     * @param array $companyCreditDatasets
     * @param array $companyPaymentDatasets
     * @param array $productsData
     * @param array $checkout
     * @param array $expectedCreditLimitRange
     * @param string|null $configData
     * @return array
     */
    public function test(
        array $customerDatasets,
        array $companyDatasets,
        array $companyCreditDatasets,
        array $companyPaymentDatasets,
        array $productsData,
        array $checkout,
        array $expectedCreditLimitRange,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customers = [];
        $companies = [];
        $orderIds = [];
        $products = $this->prepareProducts($productsData);
        if (!empty($checkout['payment']['po_number'])) {
            $checkout['payment']['po_number'] .= time();
        }
        for ($i = 0; $i < count($customerDatasets); $i++) {
            $customers[$i] = $this->fixtureFactory->createByCode(
                'customer',
                [
                    'dataset' => $customerDatasets[$i],
                ]
            );
            $customers[$i]->persist();
            $companies[$i] = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => $companyDatasets[$i],
                    'data' => [
                        'email' => $customers[$i]->getEmail(),
                    ],
                ]
            );
            $companies[$i]->persist();
            $companyPaymentFixture[$i] = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => $companyPaymentDatasets[$i]
                ]
            );
            $companyCreditFixture[$i] = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => $companyCreditDatasets[$i]
                ]
            );
            $this->companyIndex->open();
            $filter = ['company_name' => $companies[$i]->getCompanyName()];
            $this->companyIndex->getGrid()->searchAndOpen($filter);
            $this->companyEdit->getCompanyForm()->openSection('settings');
            $this->companyEdit->getCompanyForm()->fill($companyPaymentFixture[$i]);
            $this->companyEdit->getCompanyForm()->openSection('company_credit');
            $this->companyEdit->getCompanyForm()->fill($companyCreditFixture[$i]);
            $this->companyEdit->getFormPageActions()->save();
            $this->loginCustomer($customers[$i]);
            $this->addToCart([$products[$i]]);
            $orderIds[$i] = $this->placeOrder($checkout);
        }

        return [
            'creditLimits' => $expectedCreditLimitRange
        ];
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
        parent::tearDown();
    }
}
