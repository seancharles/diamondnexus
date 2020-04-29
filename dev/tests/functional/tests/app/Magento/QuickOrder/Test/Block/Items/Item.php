<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Block\Items;

use Magento\Mtf\Client\Locator;

/**
 * QuickOrder item container.
 */
class Item extends \Magento\Mtf\Block\Block
{
    /**
     * Delete button CSS selector.
     *
     * @var string
     */
    private $deleteButton = '.action.remove';

    /**
     * SKU input field CSS selector.
     *
     * @var string
     */
    private $skuInput = '.sku input';

    /**
     * Qty input field CSS selector.
     *
     * @var string
     */
    private $qtyInput = 'input.qty';

    /**
     * CSS selector for result product block.
     *
     * @var string
     */
    private $resultSelector = 'div[data-role="product-block"] > div';

    /**
     * CSS selector for preview name product.
     *
     * @var string
     */
    private $previewProductNameSelector = 'div[data-role="product-block"] div.product-name p.name a';

    /**
     * CSS selector for error.
     *
     * @var string
     */
    private $errorSelector = 'div[data-role="product-block"] div[data-role="error-message"] > div';

    /**
     * CSS selector for autocomplete block.
     *
     * @var string
     */
    private $autocompleteSelector = 'ul.ui-autocomplete';

    /**
     * CSS selector for autocomplete result list.
     *
     * @var string
     */
    private $autocompleteResultList = '.field.sku > ul.ui-autocomplete li.ui-menu-item span';

    /**
     * Get autocomplete block.
     *
     * @return \Magento\QuickOrder\Test\Block\Items\Item\Autocomplete
     */
    public function getAutocompleteBlock()
    {
        return $this->blockFactory->create(
            \Magento\QuickOrder\Test\Block\Items\Item\Autocomplete::class,
            [
                'element' => $this->_rootElement->find($this->autocompleteSelector)
            ]
        );
    }

    /**
     * Wait for visible autocomplete block.
     *
     * @return $this
     */
    public function waitAutocompleteBlockVisible()
    {
        $this->waitForElementVisible($this->autocompleteSelector);
        return $this;
    }

    /**
     * Get autocomplete result list.
     *
     * @return array
     */
    public function getAutocompleteResultList()
    {
        $this->waitForElementVisible($this->autocompleteSelector);
        $resultList = [];
        foreach ($this->_rootElement->getElements($this->autocompleteResultList) as $element) {
            $resultList[] = $element->getText();
        }
        return $resultList;
    }

    /**
     * Wait for visible result block.
     *
     * @return $this
     */
    public function waitResultVisible()
    {
        $this->waitForElementVisible($this->resultSelector);
        return $this;
    }

    /**
     * Is result block visible.
     *
     * @return bool
     */
    public function isResultVisible()
    {
        return $this->_rootElement->find($this->resultSelector)->isVisible();
    }

    /**
     * Get preview product name.
     *
     * @return string|null
     */
    public function getPreviewProductName()
    {
        $name = $this->_rootElement->find($this->previewProductNameSelector);
        return $name->getText();
    }

    /**
     * Get error.
     *
     * @return string|null
     */
    public function getError()
    {
        $error = $this->_rootElement->find($this->errorSelector);
        return $error->isVisible() ? $error->getText() : null;
    }

    /**
     * Get sku.
     *
     * @return string
     */
    public function getSku()
    {
        return $this->_rootElement->find($this->skuInput)->getValue();
    }

    /**
     * Set sku.
     *
     * @param string $sku
     * @param bool $waitAutocomplete [optional]
     * @return $this
     */
    public function setSku($sku, $waitAutocomplete = true)
    {
        $this->_rootElement->find($this->skuInput)->setValue($sku);
        if ($waitAutocomplete) {
            $this->waitResultVisible();
        }
        return $this;
    }

    /**
     * Set invalid sku.
     *
     * @param string $sku
     * @return $this
     */
    public function setInvalidSku($sku)
    {
        $this->_rootElement->find($this->skuInput)->setValue($sku);
        return $this;
    }

    /**
     * Set qty.
     *
     * @param string $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->_rootElement->find($this->qtyInput)->setValue($qty);

        return $this;
    }

    /**
     * Remove item.
     *
     * @return void
     */
    public function clickRemoveItem()
    {
        $this->_rootElement->find($this->deleteButton)->click();
    }

    /**
     * Wait for element is visible in the block.
     *
     * @param string $selector
     * @param string $strategy [optional]
     * @return bool|null
     */
    public function waitForElementVisible($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;

        return $browser->waitUntil(
            function () use ($selector, $strategy) {
                $element = $this->_rootElement->find($selector, $strategy);

                return $element->isVisible() ? true : null;
            }
        );
    }
}
