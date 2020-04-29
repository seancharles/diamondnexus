<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class RequisitionListGrid
 * Requisition List Grid block
 */
class RequisitionListGrid extends Block
{
    /**
     * CSS locator for first view link
     *
     * @var string
     */
    protected $firstView = '.action-menu-item';

    /**
     * No data css selector
     *
     * @var string
     */
    protected $noData = '.data-grid-tr-no-data';

    /**
     * Css locator for spinner
     *
     * @var string
     */
    protected $spinner = '.spinner';

    /**
     * Locator for requisition list item
     *
     * @var string
     */
    protected $requisitionList = '//tr[td/div/div[@class="cell-label-line-name" and contains(., "%s")]]';

    /**
     * Locator for requisition list view link
     *
     * @var string
     */
    protected $rlViewLink = '//a[parent::td/parent::tr/td/div/div[contains(., "%s")]]';

    /**
     * Click view link
     *
     * @return void
     */
    public function openFirstItem()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->_rootElement->find($this->firstView)->click();
    }

    /**
     * Checks if grid is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $this->waitForElementNotVisible($this->spinner);
        return (bool)count($this->_rootElement->getElements($this->noData));
    }

    /**
     * Check if requisition list is visible in grid on Storefront
     *
     * @param array $filter
     * @return bool
     */
    public function isRequisitionListVisible($filter)
    {
        $this->waitForElementNotVisible($this->spinner);
        return $this->_rootElement->find(
            sprintf($this->requisitionList, $filter['name']),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Selects requisition list by name
     *
     * @param string $rlName
     * @return void
     */
    public function openRequisitionListByName($rlName)
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->_rootElement->find(
            sprintf($this->rlViewLink, $rlName),
            Locator::SELECTOR_XPATH
        )->click();
    }
}
