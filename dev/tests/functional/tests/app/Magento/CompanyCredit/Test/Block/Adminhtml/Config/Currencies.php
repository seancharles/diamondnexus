<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Currencies configuration block on Stores > Configuration > General > Currency Setup page.
 */
class Currencies extends Block
{
    /**
     * Base Currency selector.
     *
     * @var string
     */
    private $baseCurrencySelector = "#currency_options_base";

    /**
     * Base Currency section header selector.
     *
     * @var string
     */
    private $baseCurrencyHeader = "#currency_options-head";

    /**
     * Switch website Base Currency.
     *
     * @param string $currencyCode
     * @return void
     */
    public function switchBaseCurrency($currencyCode)
    {
        if (!$this->_rootElement->find($this->baseCurrencySelector)->isVisible()) {
            $this->_rootElement->find($this->baseCurrencyHeader)->click();
        }
        $this->_rootElement->find(
            $this->baseCurrencySelector,
            Locator::SELECTOR_CSS,
            'select'
        )->setValue($currencyCode);
    }
}
