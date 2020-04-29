<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for manage company.
 */
class ManageCompany extends Block
{
    /**
     * Info message selector.
     *
     * @var string
     */
    protected $infoMessage = '//div[contains(@class, "message info")]/span';

    /**
     * "Create a Company Account" button Xpath locator.
     *
     * @var string
     */
    protected $createCompanyButton = '//div[@class="primary"]/a/span[text() = "Create a Company Account"]';

    /**
     * Get info message.
     *
     * @return string
     */
    public function getInfoMessage()
    {
        return $this->_rootElement->find($this->infoMessage, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Check if "Create a Company Account" button is visible.
     *
     * @return bool
     */
    public function isButtonVisible()
    {
        return $this->_rootElement->find($this->createCompanyButton, Locator::SELECTOR_XPATH)->isVisible();
    }
}
