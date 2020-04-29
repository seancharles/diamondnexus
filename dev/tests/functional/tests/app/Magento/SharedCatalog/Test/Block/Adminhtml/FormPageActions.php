<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Product Form page actions.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Duplicate" button.
     *
     * @var string
     */
    protected $duplicateButton = '[data-ui-id="duplicate-button"]';

    /**
     * Click "Duplicate" button.
     *
     * @return void
     */
    public function duplicate()
    {
        $this->waitForElementVisible($this->duplicateButton);
        $this->_rootElement->find($this->duplicateButton)->click();
    }
}
