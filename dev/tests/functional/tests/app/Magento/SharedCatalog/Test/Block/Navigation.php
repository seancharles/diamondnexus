<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Navigation.
 */
class Navigation extends Block
{
    /**
     * Css selector filter group.
     *
     * @var string
     */
    private $filterGroup = '#narrow-by-list [data-role="collapsible"] [data-role="title"]';

    /**
     * Css selector for filter item.
     *
     * @var string
     */
    private $filterItem = '//*[@id="narrow-by-list"]//*[@data-role="collapsible"]//li[%d]';

    /**
     * Toggle filter group.
     *
     * @return void
     */
    public function expandFilterGroup()
    {
        if (!$this->getFilterItemByIndex(1)->isVisible()) {
            $this->_rootElement->find($this->filterGroup)->click();
        }
    }

    /**
     * Get first filter item value.
     *
     * @param int $index
     * @return string
     */
    public function getFilterItemTextByIndex($index)
    {
        return trim($this->getFilterItemByIndex($index)->getText());
    }

    /**
     * Get first filter item value.
     *
     * @param int $index
     * @return SimpleElement
     */
    private function getFilterItemByIndex($index)
    {
        return $this->_rootElement->find(sprintf($this->filterItem, $index), Locator::SELECTOR_XPATH);
    }
}
