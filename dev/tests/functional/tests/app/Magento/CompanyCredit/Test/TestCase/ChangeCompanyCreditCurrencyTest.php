<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Store\Test\Fixture\Website;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company.
 * 3. Create 2 websites with different base currencies.
 * 4. Create product.
 *
 * Steps:
 * 1. Place order using payment on account payment method.
 * 2. Change credit currency.
 * 3. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68383
 */
class ChangeCompanyCreditCurrencyTest extends AbstractCompanyCreditTest
{
    /**
     * ScopeInterface::SCOPE_WEBSITE
     */
    const SCOPE_WEBSITE = 'website';

    /**
     * Website fixture.
     *
     * @var \Magento\Store\Test\Fixture\Website
     */
    private $website;

    /**
     * Allowed currencies config path.
     *
     * @var string
     */
    private $allowedCurrencies = 'currency/options/allow';

    /**
     * Base currency config path.
     *
     * @var string
     */
    private $baseCurrency = 'currency/options/base';

    /**
     * @var array
     */
    private $currenciesData;

    /**
     * Default display currency config path.
     *
     * @var string
     */
    private $displayCurrency = 'currency/options/default';

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
     * Payments configuration page.
     *
     * @var SystemConfigEditSectionPayment
     */
    private $systemConfigEditSectionPayment;

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param SystemConfigEditSectionPayment $systemConfigEditSectionPayment
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        SystemConfigEditSectionPayment $systemConfigEditSectionPayment
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->systemConfigEditSectionPayment = $systemConfigEditSectionPayment;
    }

    /**
     * Test.
     *
     * @param Customer $customer
     * @param array $productsData
     * @param array $currenciesData
     * @param string $companyCredit
     * @param string $companyPayment
     * @param array $historyData
     * @param array $amounts
     * @param array $currencyRates
     * @param bool $changeCreditCurrency
     * @param string $currencySymbol
     * @param string $creditCurrencyCode
     * @param string $creditCurrency
     * @param string|null $currencyFromCode [optional]
     * @param string|null $currencyRate [optional]
     * @param array $checkout [optional]
     * @param string|null $configData [optional]
     * @return array
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function test(
        Customer $customer,
        array $productsData,
        array $currenciesData,
        $companyCredit,
        $companyPayment,
        array $historyData,
        array $amounts,
        array $currencyRates,
        $changeCreditCurrency,
        $currencySymbol,
        $creditCurrencyCode,
        $creditCurrency,
        $currencyFromCode = null,
        $currencyRate = null,
        array $checkout = [],
        $configData = null
    ) {
        $this->currenciesData = $currenciesData;
        $this->website = $this->createWebsite();
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->setupCurrencies($this->website, $currenciesData);
        $this->objectManager->create(
            \Magento\CompanyCredit\Test\TestStep\SetInitialCurrencyRatesStep::class,
            ['currencyRates' => $currencyRates]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_status',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $companyPaymentFixture = $this->fixtureFactory->createByCode('company', ['dataset' => $companyPayment]);
        $companyCreditFixture = $this->fixtureFactory->createByCode(
            'company',
            ['dataset' => $companyCredit]
        );
        $company->persist();
        $this->enablePaymentOnAccount();
        $this->companyIndex->open();
        $filter = ['company_name' => $company->getCompanyName()];
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getCompanyForm()->openSection('settings');
        $this->companyEdit->getCompanyForm()->fill($companyPaymentFixture);
        $this->companyEdit->getCompanyForm()->openSection('company_credit');
        $this->companyEdit->getCompanyForm()->fill($companyCreditFixture);
        $this->companyEdit->getFormPageActions()->save();
        $products = $this->prepareProducts($productsData);
        $this->loginCustomer($customer);
        $this->addToCart($products);
        $this->placeOrder($checkout);

        if ($changeCreditCurrency) {
            $this->companyIndex->open();
            $this->companyIndex->getGrid()->searchAndOpen($filter);
            $this->companyEdit->getCompanyForm()->openSection('company_credit');
            $this->companyEdit->getCompanyCreditForm()->selectCurrencyInDropdown($creditCurrency);
            $this->companyEdit->getCurrencyRatePopup()->setCurrencyRate($currencyRate);
            $this->companyEdit->getCurrencyRatePopup()->cancel();
            $this->companyEdit->getCompanyCreditForm()->selectCurrencyInDropdown($creditCurrency);
            $this->companyEdit->getCurrencyRatePopup()->setCurrencyRate($currencyRate);
            $this->companyEdit->getCurrencyRatePopup()->proceed();
            $this->companyEdit->getFormPageActions()->saveAndContinue();
        }

        return [
            'historyData' => $historyData,
            'company' => $company,
            'amounts' => $amounts,
            'currencySymbol' => $currencySymbol,
            'creditCurrency' => $creditCurrency,
            'companies' => [$company],
            'rates' => [$currencyRate],
            'creditCurrencyCode' => $creditCurrencyCode,
            'currencyToCode' => $creditCurrencyCode,
            'currencyFromCode' => $currencyFromCode
        ];
    }

    /**
     * Change currencies settings.
     *
     * @param Website $website
     * @param array $currenciesData
     * @return void
     */
    private function setupCurrencies(Website $website, array $currenciesData)
    {
        $paramsMainWebsite = [
            'data' => [
                $this->allowedCurrencies => [
                    'value' => $currenciesData['allowedCurrenciesMainWebsite']
                ],
                $this->baseCurrency => [
                    'value' => $currenciesData['baseCurrencyMainWebsite']
                ],
                $this->displayCurrency => [
                    'value' => $currenciesData['displayCurrencyMainWebsite']
                ],
            ]
        ];
        $this->saveConfig($paramsMainWebsite);
        $paramsSecondWebsite = [
            'data' => [
                $this->allowedCurrencies => [
                    'value' => $currenciesData['allowedCurrenciesSecondWebsite']
                ],
                $this->baseCurrency => [
                    'value' => $currenciesData['baseCurrencySecondWebsite']
                ],
                $this->displayCurrency => [
                    'value' => $currenciesData['displayCurrencySecondWebsite']
                ],
                'scope' => [
                    'fixture' => $website,
                    'scope_type' => self::SCOPE_WEBSITE,
                    'website_id' => $website->getWebsiteId(),
                    'set_level' => self::SCOPE_WEBSITE
                ]
            ]
        ];
        $this->saveConfig($paramsSecondWebsite);
    }

    /**
     * Set default currencies settings.
     *
     * @param array $currenciesData
     * @return void
     */
    private function setDefaultCurrenciesSettings(array $currenciesData)
    {
        $params = [
            'data' => [
                $this->allowedCurrencies => [
                    'value' => $currenciesData['initialAllowedCurrenciesMainWebsite']
                ],
                $this->baseCurrency => [
                    'value' => $currenciesData['initialBaseCurrencyMainWebsite']
                ],
                $this->displayCurrency => [
                    'value' => $currenciesData['initialDisplayCurrencyMainWebsite']
                ],
            ]
        ];
        $this->saveConfig($params);
    }

    /**
     * Save config value.
     *
     * @param array $config
     * @return void
     */
    private function saveConfig(array $config)
    {
        $configMainWebsite = $this->fixtureFactory->createByCode('configData', $config);
        $configMainWebsite->persist();
    }

    /**
     * Enable payment on account payment method.
     *
     * @return void
     */
    private function enablePaymentOnAccount()
    {
        $this->systemConfigEditSectionPayment->open();
        $this->systemConfigEditSectionPayment->getPaymentAccount()->enable();
        $this->systemConfigEditSectionPayment->getPageActions()->save();
    }

    /**
     * Create custom website.
     *
     * @return Website
     */
    private function createWebsite()
    {
        /** @var \Magento\Store\Test\Fixture\Website $websiteFixture */
        $websiteFixture = $this->fixtureFactory->createByCode('website', ['dataset' => 'custom_website']);
        $websiteFixture->persist();
        $storeGroupFixture = $this->fixtureFactory->createByCode(
            'storeGroup',
            [
                'data' => [
                    'website_id' => [
                        'fixture' => $websiteFixture
                    ],
                    'root_category_id' => [
                        'dataset' => 'default_category'
                    ],
                    'name' => 'Store_Group_%isolation%',
                ]
            ]
        );
        $storeGroupFixture->persist();
        /** @var \Magento\Store\Test\Fixture\Store $storeFixture */
        $storeFixture = $this->fixtureFactory->createByCode(
            'store',
            [
                'data' => [
                    'website_id' => $websiteFixture->getWebsiteId(),
                    'group_id' => [
                        'fixture' => $storeGroupFixture
                    ],
                    'is_active' => true,
                    'name' => 'Store_%isolation%',
                    'code' => 'store_%isolation%'
                ]
            ]
        );
        $storeFixture->persist();

        return $websiteFixture;
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\CompanyCredit\Test\TestStep\DeleteWebsiteStep::class,
            ['website' => $this->website]
        )->run();
        $this->setDefaultCurrenciesSettings($this->currenciesData);
        parent::tearDown();
    }
}
