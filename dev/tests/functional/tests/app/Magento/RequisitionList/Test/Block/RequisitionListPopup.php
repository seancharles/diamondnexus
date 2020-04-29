<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Requisition list popup on Storefront.
 */
class RequisitionListPopup extends Form
{
    /**
     * Save button selector.
     *
     * @var string
     */
    protected $saveSelector = '.action.confirm';

    /**
     * Replace button selector.
     *
     * @var string
     */
    protected $replaceSelector = '.action.replace';

    /**
     * Css locator for spinner.
     *
     * @var string
     */
    protected $spinner = '.spinner, [data-role="spinner"]';

    /**
     * Fill form.
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillForm(array $data, SimpleElement $element = null)
    {
        $fields = isset($data['fields']) ? $data['fields'] : $data;
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Save requisition list.
     *
     * @return void
     */
    public function confirm()
    {
        $this->clickButton($this->saveSelector);
    }

    /**
     * Replace items.
     *
     * @return void
     */
    public function replace()
    {
        $this->clickButton($this->replaceSelector);
    }

    /**
     * Click button.
     *
     * @param string $buttonSelector
     * @return void
     */
    private function clickButton($buttonSelector)
    {
        if ($this->_rootElement->find($buttonSelector)->isVisible()) {
            $this->_rootElement->find($buttonSelector)->click();
        }
        $this->waitForElementNotVisible($this->spinner);
    }
}
