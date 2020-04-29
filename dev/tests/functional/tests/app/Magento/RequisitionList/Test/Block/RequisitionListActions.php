<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Class RequisitionListActions
 * Requisition List Grid actions block
 */
class RequisitionListActions extends Block
{
    /**
     * CSS locator for "Create New Requisition List" link
     *
     * @var string
     */
    protected $createNew = '.action.add';

    /**
     * Css locator for spinner
     *
     * @var string
     */
    protected $spinner = '.spinner';

    /**
     * Click "Create New Requisition List" link
     *
     * @return void
     */
    public function clickCreateButton()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->_rootElement->find($this->createNew)->click();
    }

    /**
     * Check if "Create New Requisition List" link is visible
     *
     * @return bool
     */
    public function checkCreateLinkIsVisible()
    {
        return $this->_rootElement->find($this->createNew)->isVisible();
    }
}
