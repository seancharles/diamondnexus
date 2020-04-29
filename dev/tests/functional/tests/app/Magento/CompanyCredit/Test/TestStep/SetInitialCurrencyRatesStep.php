<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestStep;

use Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex;

/**
 * Set initial currency rates.
 */
class SetInitialCurrencyRatesStep implements \Magento\Mtf\TestStep\TestStepInterface
{
    /**
     * Currency rate index page.
     *
     * @var \Magento\CurrencySymbol\Test\Page\Adminhtml\SystemCurrencyIndex
     */
    private $currencyIndexPage;

    /**
     * Currency rates.
     *
     * @var array
     */
    private $currencyRates;

    /**
     * SetInitialCurrencyRatesStep constructor.
     *
     * @param SystemCurrencyIndex $currencyIndexPage
     * @param array $currencyRates
     */
    public function __construct(
        SystemCurrencyIndex $currencyIndexPage,
        array $currencyRates
    ) {

        $this->currencyIndexPage = $currencyIndexPage;
        $this->currencyRates = $currencyRates;
    }

    /**
     * Set initial currency rates.
     */
    public function run()
    {
        $this->currencyIndexPage->open();

        foreach ($this->currencyRates as $currencyRateData) {
            $currencyFrom = $currencyRateData['currency_from'];
            $currencyTo = $currencyRateData['currency_to'];
            $rate = $currencyRateData['rate'];
            $this->currencyIndexPage->getSystemCurrencyRates()->setCurrencyRate($currencyFrom, $currencyTo, $rate);
        }

        $this->currencyIndexPage->getFormPageActions()->save();
    }
}
