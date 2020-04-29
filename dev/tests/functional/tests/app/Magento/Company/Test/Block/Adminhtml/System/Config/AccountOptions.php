<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Account options block on Stores > Configuration > Customer Configuration > Create New Account Options
 */
class AccountOptions extends Block
{
    /**
     * XPath locator for default group.
     *
     * @var string
     */
    private $defaultGroup = '//*[@id="customer_create_account_default_group"] //option[@selected]';

    /**
     * XPath locator for customer create account link.
     *
     * @var string
     */
    private $customerCreateAccountLink = '//*[@id="customer_create_account-head"]';

    /**
     * XPath locator for customer create account tab.
     *
     * @var string
     */
    private $customerCreateAccountTab = '//*[@id="customer_create_account"]';

    /**
     * Get default group.
     *
     * @return string
     */
    public function getDefaultGroup()
    {
        if (!$this->_rootElement->find($this->customerCreateAccountTab, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->customerCreateAccountLink, Locator::SELECTOR_XPATH)->click();
        }
        return $this->_rootElement->find($this->defaultGroup, Locator::SELECTOR_XPATH)->getText();
    }
}
