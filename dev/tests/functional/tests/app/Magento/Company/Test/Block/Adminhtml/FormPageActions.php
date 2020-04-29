<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Product Form page actions.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * CSS selector toggle "Save button".
     *
     * @var string
     */
    private $toggleButton = '[data-ui-id="save-button-dropdown"]';

    /**
     * Save type item.
     *
     * @var string
     */
    private $saveAndCloseButton = '[data-ui-id="save-button-item-0"]';

    /**
     * "Save" button.
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="save-button"]';

    /**
     * Click save (Save & close button).
     *
     * @return void
     */
    public function save()
    {
        $this->_rootElement->find($this->toggleButton)->click();
        $this->_rootElement->find($this->saveAndCloseButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click save and continue edit (Save button).
     *
     * @return void
     */
    public function saveAndContinue()
    {
        $this->_rootElement->find($this->saveButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }
}
