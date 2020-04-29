<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Block\Items\Item;

use Magento\Mtf\Client\Locator;

/**
 * Class Autocomplete
 */
class Autocomplete extends \Magento\Mtf\Block\Block
{
    /**
     * @var string
     */
    private $suggestTextSelector = './/a[contains(text(), "%s")]';

    /**
     * Select suggestion
     *
     * @param $text
     * @return $this
     */
    public function selectSuggestion($text)
    {
        $this->_rootElement->find(
            sprintf($this->suggestTextSelector, $text),
            Locator::SELECTOR_XPATH
        )->click();
        return $this;
    }
}
