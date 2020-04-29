<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Class CustomerGroup.
 */
class CustomerGroup extends SimpleElement
{
    /**
     * Selector for option.
     *
     * @var string
     */
    private $customerGroup = './/*[text()[contains(., "%s")]]';

    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    private $customerGroupField = '[data-action="open-search"]';

    /**
     * Selector for selected customer group value.
     *
     * @var string
     */
    private $customerGroupValue = '[name$="[cust_group]"] div[data-role="selected-option"]';

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->find($this->customerGroupField, Locator::SELECTOR_CSS)->click();
        $this->find(sprintf($this->customerGroup, $value), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get selected value.
     *
     * @return array|string
     */
    public function getValue()
    {
        return $this->find($this->customerGroupValue, Locator::SELECTOR_CSS)->getText();
    }
}
