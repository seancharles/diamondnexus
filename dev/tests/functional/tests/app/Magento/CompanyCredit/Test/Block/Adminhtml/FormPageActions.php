<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Form Page Actions for company.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Reimburse Balance" button.
     *
     * @var string
     */
    private $reimburseBalanceButton = '[data-ui-id="company-edit-reimburse-button-button"]';

    /**
     * Click "Reimburse Balance" button.
     *
     * @return void
     */
    public function reimburseBalance()
    {
        $this->waitForElementVisible($this->reimburseBalanceButton);
        $this->_rootElement->find($this->reimburseBalanceButton)->click();
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        $this->waitForElementNotVisible($this->loaderOld, Locator::SELECTOR_XPATH);
    }
}
