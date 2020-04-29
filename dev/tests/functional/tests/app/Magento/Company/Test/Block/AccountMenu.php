<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class AccountMenu.
 */
class AccountMenu extends Block
{
    /**
     * Xpath locator menu link.
     *
     * @var string
     */
    private $menuLink = '//ul[@class="nav items"]/li/a[contains(text(), "%s")]';

    /**
     * Xpath locator menu items.
     *
     * @var string
     */
    private $menuItems = '//ul[@class="nav items"]/li';

    /**
     * Is menu link presented.
     *
     * @param string $linkText
     * @return bool
     */
    public function isMenuLinkPresented($linkText)
    {
        return $this->_rootElement->find(sprintf($this->menuLink, $linkText), Locator::SELECTOR_XPATH)->isVisible();
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
            $menuItems[] = $item->getText() ? $item->getText() : "Delimiter";
        }
        return $menuItems;
    }
}
