<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Element;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\MultiselectElement;

/**
 * Typified element class for select with checkboxes.
 */
class MultipleElement extends MultiselectElement
{
    /**
     * Name customer group for not logged users.
     */
    const CUSTOMER_GROUP_NOT_LOGGED_IN = 'NOT LOGGED IN';

    /**
     * Selector for expanding dropdown.
     *
     * @var string
     */
    private $toggle = '[data-action="open-search"]';

    /**
     * Selector for button "Done".
     *
     * @var string
     */
    private $buttonDone = '[data-bind="click: applyChange"]';

    /**
     * Selector for button "Cancel".
     *
     * @var string
     */
    private $buttonCancel = '[data-bind="click: cancelChange"]';

    /**
     * Selector for link "Select all".
     *
     * @var string
     */
    private $selectAll = '[data-action="select-all"]';

    /**
     * Selector for link "Deselect all".
     *
     * @var string
     */
    private $deselectAll = '[data-action="deselect-all"]';

    /**
     * Selected option selector.
     *
     * @var string
     */
    private $selectedValue = '//div[contains(@class, \'_selected\')]//span';

    /**
     * Selector for search.
     *
     * @var string
     */
    private $search = '.admin__control-text.admin__action-multiselect-search';

    /**
     * Selector for search.
     *
     * @var string
     */
    private $searchReset = '.admin__action-multiselect-remove-label';

    /**
     * Option locator by value.
     *
     * @var string
     */
    protected $optionByValue = './/li//label[contains(normalize-space(.), %s)]';

    /**
     * Set values.
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->find($this->toggle)->click();
        $values = is_array($values) ? $values : [$values];
        foreach ($values as $value) {
            $this->search($value);
            $this->find(
                sprintf($this->optionByValue, $this->escapeQuotes($value)),
                Locator::SELECTOR_XPATH
            )->click();
            $this->searchReset();
        }
        $this->find($this->buttonDone)->click();
    }

    /**
     * Get values.
     *
     * @return array
     */
    public function getValue()
    {
        $this->find($this->toggle)->click();
        $values = [];
        $options = $this->getElements($this->selectedValue, Locator::SELECTOR_XPATH);
        foreach ($options as $option) {
            $values[] = $option->getText();
        }
        $this->find($this->buttonCancel)->click();
        return $values;
    }

    /**
     * Select all options in the element.
     *
     * @return void
     */
    public function selectAll()
    {
        $this->find($this->selectAll)->click();
    }

    /**
     * Deselect all options in the element.
     *
     * @return void
     */
    public function deselectAll()
    {
        $this->find($this->deselectAll)->click();
    }

    /**
     * Search in customer group field.
     *
     * @param string $value
     * @return void
     */
    public function search($value)
    {
        if ($value == self::CUSTOMER_GROUP_NOT_LOGGED_IN) {
            $value = 'NOT';
        }
        $this->find($this->search)->setValue($value);
    }

    /**
     * Reset search field.
     *
     * @return void
     */
    public function searchReset()
    {
        $this->find($this->searchReset)->click();
    }
}
