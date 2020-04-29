<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Header.
 */
class Header extends Block
{
    /**
     * Xpath locator menu link.
     *
     * @var string
     */
    private $menuLink = '//span[@class="customer-name"]';

    /**
     * Xpath locator menu items.
     *
     * @var string
     */
    private $menuItems = '//li[@class="customer-welcome active"]//div[@class="customer-menu"]' .
                         '//ul[@class="header links"]//li//a';

    /**
     * Click header menu.
     *
     * @return void
     */
    public function clickMenu()
    {
        return $this->_rootElement->find($this->menuLink, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get menu items.
     *
     * @return array
     */
    public function getMenuItems()
    {
        $menuItems = [];
        $items = $this->_rootElement->getElements($this->menuItems, Locator::SELECTOR_XPATH);
        foreach ($items as $item) {
            $menuItems[] = $item->getText();
        }
        return $menuItems;
    }
}
