<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\CustomerGroup;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Block for customer group field.
 */
class Field extends Block
{
    /**
     * "Customer Group Price" button selector.
     *
     * @var string
     */
    private $customerGroupPriceButton = '[data-action="add_new_row"]';

    /**
     * Selector for open select.
     *
     * @var string
     */
    private $openSelect = '[data-action="open-search"]';

    /**
     * Selector for input in customer group field.
     *
     * @var string
     */
    private $selectSearchField = '.admin__control-text.admin__action-multiselect-search';

    /**
     * Selector from result from customer group field.
     *
     * @var string
     */
    private $resultFromCustomerGroupField = '.admin__action-multiselect-menu-inner li[data-role="option"]';

    /**
     * Open "Customer Group Price".
     */
    public function openCustomerGroupPrice()
    {
        $this->_rootElement->find($this->customerGroupPriceButton)->click();
    }

    /**
     * Search in customer group field.
     *
     * @param string $customerGroupName
     * @return void
     */
    public function searchGroupByName($customerGroupName)
    {
        $this->_rootElement->find($this->openSelect)->click();
        $this->_rootElement->find($this->selectSearchField)->setValue($customerGroupName);
    }

    /**
     * Get result from customer group field.
     *
     * @return string
     */
    public function getResultFromField()
    {
        return $this->_rootElement->find($this->resultFromCustomerGroupField)->getText();
    }
}
