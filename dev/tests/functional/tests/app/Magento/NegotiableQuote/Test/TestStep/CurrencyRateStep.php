<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\ObjectManager;

/**
 * Create currency rate.
 */
class CurrencyRateStep implements TestStepInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param ObjectManager $objectManager
     * @param string $currency
     */
    public function __construct(
        ObjectManager $objectManager,
        string $currency
    ) {
        $this->objectManager = $objectManager;
        $this->currency = $currency;
    }

    /**
     * Create currency rate.
     *
     * @return array
     */
    public function run()
    {
        $currencyRate = $this->objectManager->getInstance()->create(
            \Magento\Directory\Test\Fixture\CurrencyRate::class,
            ['dataset' => $this->currency]
        );
        $currencyRate->persist();

        return ['currencyRate' => $currencyRate];
    }
}
