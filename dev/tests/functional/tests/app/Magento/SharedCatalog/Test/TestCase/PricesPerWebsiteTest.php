<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Util\Command\Cli\Queue;

/**
 * Preconditions:
 * 1. Create custom website.
 * 2. Set different base currencies for the Main and custom websites.
 * 3. Create product assigned to both websites.
 * 4. Create shared catalog.
 * 5. Assign product to the shared catalog.
 *
 * Steps:
 * 1. Open shared catalog in the AP.
 * 2. Switch website scope and validate currency symbols for each website.
 * 3. Open product tier prices configuration in the shared catalog.
 * 4. Switch website scope and validate currency symbols for each website.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68593, MAGETWO-68581
 */
class PricesPerWebsiteTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Simple product fixture.
     *
     * @var CatalogProductSimple
     */
    private $product;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Cli launcher for queue.
     *
     * @var Queue
     */
    private $queue;

    /**
     * Inject pages.
     *
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $stepFactory
     * @param Queue $queue
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        TestStepFactory $stepFactory,
        Queue $queue
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->stepFactory = $stepFactory;
        $this->queue = $queue;
    }

    /**
     * Set different base currencies and prices for multiple websites.
     *
     * @param SharedCatalog $sharedCatalog
     * @param CatalogProductSimple $product
     * @param string $configData
     * @param array|null $currencies [optional]
     * @param array|null $data [optional]
     * @return array
     */
    public function test(
        SharedCatalog $sharedCatalog,
        CatalogProductSimple $product,
        $configData,
        array $currencies = [],
        array $data = []
    ) {
        $this->queue->run('sharedCatalogUpdatePrice');
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalog->persist();
        $product->persist();
        $this->product = $product;
        $websites = $product->getDataFieldConfig('website_ids')['source']->getWebsites();
        if ($currencies) {
            $this->setBaseCurrencies($websites, $currencies);
        }
        $this->stepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $sharedCatalog, 'products' => [$product], 'data' => $data ?: []]
        )->run();

        return [
            'websites' => $websites
        ];
    }

    /**
     * Set base currencies for websites.
     *
     * @param array $websites
     * @param array $currencies
     * @return void
     */
    private function setBaseCurrencies(array $websites, array $currencies)
    {
        foreach ($websites as $key => $website) {
            $configFixture = $this->fixtureFactory->createByCode(
                'configData',
                [
                    'data' => [
                        'currency/options/allow' => [
                            'value' =>  $currencies[$key]['allowedCurrencies']
                        ],
                        'currency/options/base' => [
                            'value' => $currencies[$key]['baseCurrency']
                        ],
                        'scope' => [
                            'fixture' => $website,
                            'scope_type' => 'website',
                            'website_id' => $website->getWebsiteId(),
                            'set_level' => 'website',
                        ]
                    ]
                ]
            );
            $configFixture->persist();
        }
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        if (isset($this->product)) {
            $this->setBaseCurrencies(
                [$this->product->getDataFieldConfig('website_ids')['source']->getWebsites()[0]],
                [
                    [
                        'allowedCurrencies' => ['USD'],
                        'baseCurrency' => 'USD',
                    ]
                ]
            );
        }
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
