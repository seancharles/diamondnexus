<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Order history block on My Order page
 */
class History extends Block
{
    /**
     * Item order
     *
     * @var string
     */
    protected $itemOrder = '//tr[td[contains(@class, "id") and normalize-space(.)="%s"]]';

    /**
     * Reorder button css selector
     *
     * @var string
     */
    protected $reorderButton = '.action.order';

    /**
     * Order history form selector.
     *
     * @var string
     */
    protected $formSelector = '#my-orders-table';

    /**
     * Get item order block
     *
     * @param string $id
     * @return SimpleElement
     */
    protected function searchOrderById($id)
    {
        return $this->_rootElement->find(sprintf($this->itemOrder, $id), Locator::SELECTOR_XPATH);
    }

    /**
     * Reorder item.
     *
     * @param string $id
     * @return void
     */
    public function reorderQuote($id)
    {
        $this->waitFormToLoad();
        $this->searchOrderById($id)->find($this->reorderButton)->click();
    }

    /**
     * Wait order history form to load via ajax
     *
     * @return void
     */
    protected function waitFormToLoad()
    {
        $browser = $this->browser;
        $selector = $this->formSelector;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() ? true : null;
            }
        );
    }
}
