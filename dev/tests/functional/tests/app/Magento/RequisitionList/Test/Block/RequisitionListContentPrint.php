<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Class RequisitionListContentPrint
 * Requisition list content print
 */
class RequisitionListContentPrint extends Form
{
    /**
     * Product item sku selector
     *
     * @var string
     */
    protected $sku = '.product-item-sku span';

    /**
     * Logo css selector
     *
     * @var string
     */
    protected $logo = '.logo';

    /**
     * Requisition list name css selector
     *
     * @var string
     */
    protected $rlName = '.requisition-list-title h1';

    /**
     * Requisition list name block selector.
     *
     * @var string
     */
    private $requisitionListBlockSelector = './/div[contains(@class,"requisition-list-title") and contains(.,"%s")]';

    /**
     * Retrieve sku list
     *
     * @return array
     */
    public function getSkuList()
    {
        $skuArr = [];
        $elements = $this->_rootElement->getElements($this->sku);
        foreach ($elements as $element) {
            $skuArr[] = $element->getText();
        }
        return $skuArr;
    }

    /**
     * Check if logo is visible
     *
     * @return bool
     */
    public function isLogoVisible()
    {
        return $this->_rootElement->find($this->logo)->isVisible();
    }

    /**
     * Ger requisition list name
     *
     * @return string
     */
    public function getRequisitionListName()
    {
        return $this->_rootElement->find($this->rlName)->getText();
    }

    /**
     * Wait for requisition list name block to appear.
     *
     * @param string $requisitionListName
     * @return bool|null
     */
    public function waitForRequisitionListNameBlock($requisitionListName)
    {
        $this->waitForElementVisible(
            sprintf($this->requisitionListBlockSelector, $requisitionListName),
            Locator::SELECTOR_XPATH
        );
    }
}
