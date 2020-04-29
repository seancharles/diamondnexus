<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * QuickOrder items container.
 */
class Items extends Block
{
    /**
     * SKU input field CSS selector.
     *
     * @var string
     */
    private $skuInput = '.sku input';

    /**
     * CSS locator form title.
     *
     * @var string
     */
    private $formTitle = '.field.sku label';

    /**
     * Item blocks selector.
     *
     * @var string
     */
    private $itemsSelector = 'div.deletable-item';

    /**
     * Item block selector.
     *
     * @var string
     */
    private $itemSelector = '//div[@class="fields additional deletable-item"][%d]';

    /**
     * Wait for block is visible.
     *
     * @return $this
     */
    public function waitForBlockInit()
    {
        $this->waitForElementVisible($this->itemsSelector);
        return $this;
    }

    /**
     * Get items blocks.
     *
     * @return Items\Item[]
     */
    public function getItemsBlocks()
    {
        $blocks = [];
        $elements = $this->_rootElement->getElements($this->itemsSelector, Locator::SELECTOR_CSS);
        foreach ($elements as $element) {
            $blocks[] = $this->blockFactory->create(
                \Magento\QuickOrder\Test\Block\Items\Item::class,
                ['element' => $element]
            );
        }
        array_pop($blocks);
        return $blocks;
    }

    /**
     * Get item block.
     *
     * @param int $position [optional]
     * @return Items\Item
     */
    public function getItemBlock($position = 1)
    {
        return $this->blockFactory->create(
            \Magento\QuickOrder\Test\Block\Items\Item::class,
            [
                'element' => $this->_rootElement->find(
                    sprintf($this->itemSelector, $position),
                    Locator::SELECTOR_XPATH
                )
            ]
        );
    }

    /**
     * Get last item block.
     *
     * @return Items\Item
     */
    public function getLastItemBlock()
    {
        $elements = $this->_rootElement->getElements($this->itemsSelector, Locator::SELECTOR_CSS);
        return $this->getItemBlock(count($elements));
    }

    /**
     * Fill quick order sku lines.
     *
     * @param array $products
     * @return void
     */
    public function fill(array $products)
    {
        $id = 1;

        foreach ($products as $product) {
            $itemBlock = $this->getItemBlock($id);
            $itemBlock->setSku($product->getSku());
            $id++;
            $this->_rootElement->click();
            $itemBlock->waitResultVisible();
        }
    }

    /**
     * Select the first element from list of results.
     *
     * @return void
     */
    public function selectFirstItem()
    {
        $this->_rootElement->click();
    }

    /**
     * Focus out from form input.
     *
     * @return void
     */
    public function focusOutFromInput()
    {
        $this->_rootElement->find($this->formTitle)->click();
        $this->getItemBlock()->waitResultVisible();
    }

    /**
     * Remove first item from sku lines.
     *
     * @return void
     */
    public function removeFirstItem()
    {
        $this->getItemBlock()->clickRemoveItem();
    }

    /**
     * Get first sku field value.
     *
     * @return string
     */
    public function getFirstSku()
    {
        return $this->_rootElement->find($this->skuInput)->getValue();
    }
}
