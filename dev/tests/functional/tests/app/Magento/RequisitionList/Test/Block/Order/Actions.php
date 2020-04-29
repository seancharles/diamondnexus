<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block\Order;

use Magento\Mtf\Block\Block;

/**
 * Class Actions
 * Customer order view actions block
 */
class Actions extends Block
{
    /**
     * Locator value for "Add to Requisition List" select
     *
     * @var string
     */
    protected $addToRequisitionListSelect = '.requisition-list-button';

    /**
     * Locator for "Add to Requisition List" button in Requisition list select
     *
     * @var string
     */
    protected $createNewButton = '.action.new';

    /**
     * Click "Create New Requisition List" link
     *
     * @return void
     */
    public function clickCreateButton()
    {
        $this->_rootElement->find($this->addToRequisitionListSelect)->click();
        $this->_rootElement->find($this->createNewButton)->click();
    }
}
