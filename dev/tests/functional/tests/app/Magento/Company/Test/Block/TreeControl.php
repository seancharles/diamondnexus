<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Tree controls block.
 */
class TreeControl extends Block
{
    /**
     * Add customer link selector.
     *
     * @var string
     */
    protected $addCustomerLink = '#add-customer';

    /**
     * Add team link selector.
     *
     * @var string
     */
    protected $addTeamLink = '#add-team';

    /**
     * Edit selected link selector.
     *
     * @var string
     */
    protected $editSelectedLink = '#edit-selected';

    /**
     * Delete selected link selector.
     *
     * @var string
     */
    protected $deleteSelectedLink = '#delete-selected';

    /**
     * Loading mask selector.
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Unpopulated selector.
     *
     * @var string
     */
    protected $unpopulated = '.unpopulated';

    /**
     * Company Admin selector.
     *
     * @var string
     */
    protected $companyAdmin = '.company-admin';

    /**
     * Click add user link.
     *
     * @return void
     */
    public function clickAddCustomer()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->addCustomerLink)->click();
    }

    /**
     * Click add team link.
     *
     * @return void
     */
    public function clickAddTeam()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->waitForElementVisible($this->companyAdmin);
        $this->_rootElement->find($this->addTeamLink)->click();
    }

    /**
     * Click edit selected link.
     *
     * @return void
     */
    public function clickEditSelected()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->editSelectedLink)->click();
        $this->waitForElementNotVisible($this->loadingMask);
        $this->waitForElementNotVisible($this->unpopulated);
    }

    /**
     * Click delete selected link.
     *
     * @return void
     */
    public function clickDeleteSelected()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->waitForElementVisible($this->companyAdmin);
        $this->_rootElement->find($this->deleteSelectedLink)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
