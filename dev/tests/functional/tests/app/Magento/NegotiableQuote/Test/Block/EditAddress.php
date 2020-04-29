<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Form;

/**
 * Class EditAddress
 * Quote edit address popup
 */
class EditAddress extends Form
{
    /**
     * Loading mask css selector
     *
     * @var string
     */
    protected $loader = '.loading-mask';

    /**
     * Save button css selector
     *
     * @var string
     */
    protected $saveButton = '.action.primary';

    /**
     * Clicks save button
     */
    public function save()
    {
        $this->_rootElement->find($this->saveButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }
}
