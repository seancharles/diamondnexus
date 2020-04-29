<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Company\Grid;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Convert credit popup block.
 */
class ConvertCreditPopup extends Block
{
    /**
     * Proceed button CSS selector.
     *
     * @var string
     */
    private $proceedButton = '.action-primary';

    /**
     * Loading mask CSS selector.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Conversion rates input fields CSS selector.
     *
     * @var string
     */
    private $inputElements = 'input';

    /**
     * Currency select CSS selector.
     *
     * @var string
     */
    private $currencySelect = 'select[name="currency"]';

    /**
     * Fill credit conversion form with rates.
     *
     * @param string $currencyCode
     * @param array $rates [optional]
     * @return void
     */
    public function fillForm($currencyCode, array $rates = [])
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->currencySelect, Locator::SELECTOR_CSS, 'select')->setValue($currencyCode);
        $this->waitForElementNotVisible($this->loader);
        if ($rates) {
            $elements = $this->_rootElement->getElements($this->inputElements);
            for ($i = 0; $i < count($rates); $i++) {
                $elements[$i]->setValue($rates[$i]);
            }
        }
    }

    /**
     * Send credit conversion form with rates.
     *
     * @return void
     */
    public function sendForm()
    {
        $this->_rootElement->find($this->proceedButton)->click();
    }
}
