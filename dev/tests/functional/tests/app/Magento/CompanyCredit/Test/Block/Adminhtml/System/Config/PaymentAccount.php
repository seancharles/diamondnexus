<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Account options block on Stores > Configuration > Payment Methods > Payment on Account.
 */
class PaymentAccount extends Block
{
    /**
     * XPath locator for "Payment on Account" enabled.
     *
     * @var string
     */
    private $paymentAccountEnabled = '//*[@id="payment_us_companycredit_active"]';

    /**
     * XPath locator for "Payment on Account" system value .
     *
     * @var string
     */
    private $paymentAccountEnabledInherit = '//*[@id="payment_us_companycredit_active_inherit"]';

    /**
     * XPath locator for "Payment on Account" link.
     *
     * @var string
     */
    private $paymentUsCompanyCreditLink = '//*[@id="payment_us_companycredit-head"]';

    /**
     * XPath locator for "Payment on Account" tab.
     *
     * @var string
     */
    private $paymentUsCompanyCreditTab = '//*[@id="payment_us_companycredit"]';

    /**
     * XPath locator for "Other Payment Methods" tab.
     *
     * @var string
     */
    private $otherPaymentMethodsTab = '//*[@id="payment_us_other_payment_methods-head"]';

    /**
     * Enabled payment account.
     *
     * @return string
     */
    public function enable()
    {
        if (!$this->_rootElement->find($this->paymentUsCompanyCreditLink, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->otherPaymentMethodsTab, Locator::SELECTOR_XPATH)->click();
        }
        if (!$this->_rootElement->find($this->paymentUsCompanyCreditTab, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->paymentUsCompanyCreditLink, Locator::SELECTOR_XPATH)->click();
        }
        if ($this->_rootElement->find($this->paymentAccountEnabledInherit, Locator::SELECTOR_XPATH)->isSelected()) {
            $this->_rootElement->find($this->paymentAccountEnabledInherit, Locator::SELECTOR_XPATH)->click();
        }
        $this->_rootElement->find($this->paymentAccountEnabled, Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find($this->paymentAccountEnabled . '/option[@value=1]', Locator::SELECTOR_XPATH)->click();
    }
}
