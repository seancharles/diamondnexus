<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Config;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Page actions in currency config page.
 */
class PageActions extends AbstractFormPageActions
{
    /**
     * Scope CSS selector.
     *
     * @var string
     */
    private $scopeSelector = '.store-switcher .actions.dropdown';

    /**
     * Confirmation modal css selector.
     *
     * @var string
     */
    private $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Company credit update link.
     *
     * @var string
     */
    private $creditUpdateLink = '#update-credit-link';

    /**
     * Loading mask CSS selector.
     *
     * @var string
     */
    protected $loader = '.admin__data-grid-loading-mask';

    /**
     * Select website.
     *
     * @param string $websiteName
     * @return void
     */
    public function selectWebsite($websiteName)
    {
        $this->_rootElement->find($this->scopeSelector, Locator::SELECTOR_CSS, 'liselectstore')
            ->setValue($websiteName);

        $element = $this->browser->find($this->confirmModal);
        $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
        $modal->acceptAlert();
    }

    /**
     * Click credit update link.
     *
     * @return void
     */
    public function clickCreditUpdateLink()
    {
        $this->browser->find($this->creditUpdateLink)->click();
        $this->waitForElementNotVisible($this->loader);
    }
}
