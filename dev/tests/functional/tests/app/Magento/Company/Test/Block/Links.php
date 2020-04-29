<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Client\Locator;

/**
 * Company homepage links
 */
class Links extends \Magento\Theme\Test\Block\Links
{
    /**
     * @var string
     */
    protected $createAccountBlock = '//span[contains(text(), "Create an Account")]';

    /**
     * @var string
     */
    protected $createAccountLink = '//a[contains(text(), "Create an Account")]';

    /**
     * @var string
     */
    protected $createCustomerAccountTitle = '//a[contains(text(), "Create New Customer")]';

    /**
     * @inheritdoc
     */
    public function openCustomerCreateLink()
    {
        $customerMenu = $this->_rootElement->find($this->toggleButton);
        $customerAccount = $this->_rootElement->find($this->createAccountLink, Locator::SELECTOR_XPATH);

        if ($customerAccount->isVisible() || $customerMenu->isVisible()) {
            parent::openCustomerCreateLink();
        } else {
            $link = $this->_rootElement->find(
                $this->createCustomerAccountTitle,
                Locator::SELECTOR_XPATH
            );

            if (!$link->isVisible()) {
                $this->expandAccountCreationDropdown();
            }

            $link->click();
        }
    }

    /**
     * Expand company creation menu
     *
     * @return void
     */
    private function expandAccountCreationDropdown()
    {
        $this->_rootElement->find($this->createAccountBlock, Locator::SELECTOR_XPATH)->click();
    }
}
