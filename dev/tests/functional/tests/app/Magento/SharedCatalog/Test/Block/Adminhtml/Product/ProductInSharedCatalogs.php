<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Product;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Catalog product edit Product in Shared Catalogs section.
 */
class ProductInSharedCatalogs extends Block
{
    /**
     * Selector for expanding dropdown.
     *
     * @var string
     */
    private $toggle = '[data-action="open-search"]';

    /**
     * Selector for search.
     *
     * @var string
     */
    private $searchInput = '.admin__control-text.admin__action-multiselect-search';

    /**
     * Option locator by value.
     *
     * @var string
     */
    private $optionByValue = './/li//label[contains(normalize-space(.), %s)]';

    /**
     * Selector for button "Done".
     *
     * @var string
     */
    private $buttonDone = '[data-bind="click: applyChange"]';

    /**
     * Set shared catalog values.
     *
     * @param array $values
     * @return void
     */
    public function setSharedCatalogsValue(array $values)
    {
        $this->_rootElement->find($this->toggle)->click();

        foreach ($values as $value) {
            $this->_rootElement->find($this->searchInput)->setValue($value);
            $this->_rootElement->find(sprintf($this->optionByValue, $value), Locator::SELECTOR_XPATH)->click();
        }

        $this->_rootElement->find($this->buttonDone)->click();
    }
}
