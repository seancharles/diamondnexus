<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Block with currency rates data on system currency rates page.
 */
class SystemCurrencyRates extends Block
{
    /**
     * Css selector currency rate.
     *
     * @var string
     */
    private $currencyRate = 'input[name="rate[%s][%s]"]';

    /**
     * Set currency rate.
     *
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param string $rate
     * @return void
     */
    public function setCurrencyRate($currencyFrom, $currencyTo, $rate)
    {
        $this->_rootElement->find(sprintf($this->currencyRate, $currencyFrom, $currencyTo))->setValue($rate);
    }
}
