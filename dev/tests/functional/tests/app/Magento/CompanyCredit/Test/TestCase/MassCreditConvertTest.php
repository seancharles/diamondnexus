<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionCurrency;
use Magento\Store\Test\Fixture\Website;

/**
 * Preconditions:
 * 1. Create 3 websites.
 * 2. Set Base Currency for website 1 - Euro.
 * 3. Set Base Currency for website 2 - Russian Ruble.
 * 4. Create 3 companies and allocate credit for each of them.
 *
 * Steps:
 * 1. Change Base Currency for website 1 to US Dollar.
 * 2. Go to companies grid in the admin panel.
 * 3. Select 2 companies with credit in Euro.
 * 4. Select "Convert Credit" mass action.
 * 5. Select US Dollar as currency to convert credit to and set EUR/USD conversion rate.
 * 6. Click "Proceed" button.
 * 7. Perform assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68393
 */
class MassCreditConvertTest extends AbstractCompanyCreditTest
{
    /* tags */
    const MVP = 'yes';
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
     * Edit currency page.
     *
     * @var \Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionCurrency
     */
    private $editCurrency;

    /**
     * Websites array.
     *
     * @var array
     */
    private $websites = [];

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CompanyIndex $companyIndex
     * @param SystemConfigEditSectionCurrency $editCurrency
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CompanyIndex $companyIndex,
        SystemConfigEditSectionCurrency $editCurrency
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->companyIndex = $companyIndex;
        $this->editCurrency = $editCurrency;
    }

    /**
     * Test mass company credit conversion.
     *
     * @param array $customerDatasets
     * @param array $companyDataset
     * @param string $currencyTo
     * @param array $steps
     * @param array $rates
     * @param string|null $currencyToCode [optional]
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        array $customerDatasets,
        array $companyDataset,
        $currencyTo,
        array $steps = [],
        array $rates = [],
        $currencyToCode = '',
        $configData = null
    ) {
        // Preconditions:
        $this->websites = [];
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        foreach ($steps as $methodName => $stepData) {
            if (method_exists($this, $methodName)) {
                call_user_func_array([$this, $methodName], $stepData);
            }
        }
        $companies = $this->createCompanies($customerDatasets, $companyDataset);

        // Steps
        $this->editCurrency->open();
        $this->editCurrency->getFormPageActionsBlock()->selectWebsite($this->websites[0]->getName());
        $this->editCurrency->getCurrencyForm()->switchBaseCurrency($currencyTo);
        $this->editCurrency->getFormPageActionsBlock()->save();
        if ($rates) {
            $companiesToUpdate[] = ['company_name' => $companies[0]->getCompanyName()];
            $companiesToUpdate[] = ['company_name' => $companies[1]->getCompanyName()];
            $this->editCurrency->getFormPageActionsBlock()->clickCreditUpdateLink();
            $this->companyIndex->getGrid()->massaction($companiesToUpdate, 'Convert Credit');
            $this->companyIndex->getConvertCreditPopup()->fillForm($currencyTo, $rates);
            $this->companyIndex->getConvertCreditPopup()->sendForm();
        }

        return [
            'websiteName' => $this->websites[0]->getName(),
            'company' => $companies[0],
            'currencyToCode' => $currencyToCode,
            'companies' => [$companies[0]]
        ];
    }

    /**
     * Configure website Base Currencies.
     *
     * @param array $currencyList [optional]
     * @return void
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function configureCurrencies(array $currencyList = [])
    {
        foreach ($currencyList as $currency) {
            $currency = explode(',', $currency);
            $websiteFixture = $this->createWebsite();
            /** @var FixtureInterface $configFixture */
            $configFixture = $this->fixtureFactory->createByCode(
                'configData',
                [
                    'data' => [
                        'currency/options/allow' => [
                            'value' => $currency
                        ],
                        'currency/options/base' => [
                            'value' => $currency[1]
                        ],
                        'scope' => [
                            'fixture' => $websiteFixture,
                            'scope_type' => 'website',
                            'website_id' => $websiteFixture->getWebsiteId(),
                            'set_level' => 'website',
                        ]
                    ]
                ]
            );

            $configFixture->persist();
            $this->websites[] = $websiteFixture;
        }
    }

    /**
     * Create companies.
     *
     * @param array $customerDatasets
     * @param array $companyDataset
     * @return array
     */
    private function createCompanies(array $customerDatasets, array $companyDataset)
    {
        $companies = [];
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
                    'dataset' => $companyDataset[$i],
                    'data' => [
                        'email' => $customers[$i]->getEmail(),
                    ],
                ]
            );
            $companies[$i]->persist();
        }

        return $companies;
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
                    'code' => 'store_group_%isolation%'
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
     * Remove websites and reset system configuration to default.
     *
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->websites as $website) {
            $this->objectManager->create(
                \Magento\CompanyCredit\Test\TestStep\DeleteWebsiteStep::class,
                ['website' => $website]
            )->run();
        }
        parent::tearDown();
    }
}
