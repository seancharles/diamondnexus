<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;

/**
 * Form for creation of the company.
 */
class CompanyCreditForm extends Block
{
    /**
     * Css selector credit currency select.
     *
     * @var string
     */
    private $creditCurrencySelect = 'select[name="company_credit[currency_code]"]';

    /**
     * Xpath locator for currency symbol of credit limit.
     *
     * @var string
     */
    private $creditLimitCurrencySymbol = '//input[@name="company_credit[credit_limit]"]/../label/span';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    private $templateBlock = './ancestor::body';

    /**
     * Select currency in dropdown.
     *
     * @param string $currency
     * @return void
     */
    public function selectCurrencyInDropdown($currency)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->find($this->creditCurrencySelect, Locator::SELECTOR_CSS, 'select')->setValue($currency);
    }

    /**
     * Get credit currency value.
     *
     * @return string
     */
    public function getCreditCurrencyValue()
    {
        $this->getTemplateBlock()->waitLoader();
        return $this->_rootElement->find($this->creditCurrencySelect)->getValue();
    }

    /**
     * Get credit limit currency symbol.
     *
     * @return string
     */
    public function getCreditCurrencySymbol()
    {
        $this->getTemplateBlock()->waitLoader();
        return trim($this->_rootElement->find($this->creditLimitCurrencySymbol, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    private function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
