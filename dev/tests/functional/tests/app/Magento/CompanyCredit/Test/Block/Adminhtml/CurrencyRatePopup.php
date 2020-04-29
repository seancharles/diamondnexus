<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Currency rate popup on company edit page.
 */
class CurrencyRatePopup extends Block
{
    /**
     * CSS selector for Magento loader.
     *
     * @var string
     */
    private $loader = '[data-role="loader"]';

    /**
     * Css selector currency rate input.
     *
     * @var string
     */
    private $currencyRateInput = 'input[name="credit_limit_change[currency_rate]"]';

    /**
     * Css selector Cancel button.
     *
     * @var string
     */
    private $cancelButton = '.action-secondary';

    /**
     * Css selector Proceed button.
     *
     * @var string
     */
    private $proceedButton = '.action-primary';

    /**
     * Set currency rate.
     *
     * @param string $currencyRate
     * @return void
     */
    public function setCurrencyRate($currencyRate)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->currencyRateInput)->setValue($currencyRate);
    }

    /**
     * Click Cancel button.
     *
     * @return void
     */
    public function cancel()
    {
        $this->_rootElement->find($this->cancelButton)->click();
    }

    /**
     * Click Proceed button.
     *
     * @return void
     */
    public function proceed()
    {
        $this->_rootElement->find($this->proceedButton)->click();
    }
}
