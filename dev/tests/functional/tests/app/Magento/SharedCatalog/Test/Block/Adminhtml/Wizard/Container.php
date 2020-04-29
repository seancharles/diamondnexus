<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Mtf\Block\Block;

/**
 * Container for shared catalog configuration.
 */
class Container extends Block
{
    /** @var string */
    protected $configureButton = 'button[data-action="open-steps-wizard"]';

    /** @var string */
    protected $loaderSelector = 'div[data-component="catalog-steps-wizard"]';

    /**
     * Open configuration slide
     *
     * @return void
     */
    public function openConfigureWizard()
    {
        $this->waitForElementNotVisible($this->loaderSelector);
        $this->_rootElement->find($this->configureButton)->click();
        $this->waitForElementVisible('.modal-slide');
    }
}
