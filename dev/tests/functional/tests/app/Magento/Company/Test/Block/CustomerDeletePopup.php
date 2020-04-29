<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * User delete popup block.
 */
class CustomerDeletePopup extends Block
{
    /**
     * Loader selector.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Xpath locator Set Inactive button.
     *
     * @var string
     */
    private $setInactiveButton = '//footer[@class="modal-footer"]/button[@class="action"]';

    /**
     * Xpath locator Delete button.
     *
     * @var string
     */
    private $deleteButton = '//footer[@class="modal-footer"]/button[@class="action primary delete"]';

    /**
     * Click Set Inactive button.
     *
     * @return void
     */
    public function clickSetInactive()
    {
        $this->_rootElement->find($this->setInactiveButton, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click Delete button.
     *
     * @return void
     */
    public function clickDelete()
    {
        $this->_rootElement->find($this->deleteButton, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }
}
