<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Customer group element.
 */
class CustomerGroup extends SimpleElement
{
    /**
     * Selector for selected option.
     *
     * @var string
     */
    private $customerGroup = '[data-role="selected-option"]';

    /**
     * Selector for field.
     *
     * @var string
     */
    private $customerGroupField = '[data-action="open-search"]';

    /**
     * Selector for option.
     *
     * @var string
     */
    private $customerGroupName = './/*[text()[contains(., "%s")]]';

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->find($this->customerGroup)->getText();
    }

    /**
     * Set value.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->find($this->customerGroupField, Locator::SELECTOR_CSS)->click();
        $this->find(sprintf($this->customerGroupName, $value), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Check if element is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return strpos($this->getAttribute('class'), '_disabled') !== false;
    }
}
