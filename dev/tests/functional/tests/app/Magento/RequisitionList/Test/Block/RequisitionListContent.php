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
 * Class RequisitionListContent
 * Requisition list content on Storefront
 */
class RequisitionListContent extends Form
{
    /**
     * Product item sku selector
     *
     * @var string
     */
    protected $sku = '.product-item-sku span';

    /**
     * Edit button
     *
     * @var string
     */
    protected $editButton = '.action.action-edit';

    /**
     * Xpath selector for item to be edited
     *
     * @var string
     */
    protected $itemBox = '//form/table/tbody/tr[td/div/div/span[contains(., "%s")]]';

    /**
     * Css selector for qty input
     *
     * @var string
     */
    protected $qtyInput = '.input-text.qty';

    /**
     * Css selector for configurable product options
     *
     * @var string
     */
    protected $optionValues = '.product-item-details .item-options dd';

    /**
     * Css selector for prices including tax
     *
     * @var string
     */
    protected $price = '.price';

    /**
     * Css selector for prices excluding tax
     *
     * @var string
     */
    protected $priceExclTax = '.price-excluding-tax';

    /**
     * Css selector for requisition list items
     *
     * @var string
     */
    protected $itemSelector = '.requisition-grid .item';

    /**
     * Print link css selector
     *
     * @var string
     */
    protected $printLink = '.action.print';

    /**
     * Requisition list item checkbox css selector
     *
     * @var string
     */
    protected $selectAll = '[data-role=\'select-all\']';

    /**
     * Action button css selector
     *
     * @var string
     */
    protected $actionButton = '//button/span[contains(., "%s")]';

    /**
     * Create new requisition list option css selector
     *
     * @var string
     */
    protected $createNew = '//button/span[contains(., "%s")]/following::div/ul/li/span';

    /**
     * Add to cart button css selector
     *
     * @var string
     */
    protected $addToCartButton = '.requisition-view-buttons .action.primary';

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
     * Check if product edit link is visible
     *
     * @return bool
     */
    public function isEditLinkVisible()
    {
        return $this->_rootElement->find($this->editButton)->isVisible();
    }

    /**
     * @param string $productToUpdate
     *
     * @return void
     */
    public function clickEditButton($productToUpdate)
    {
        $row = $this->_rootElement->find(sprintf($this->itemBox, $productToUpdate), Locator::SELECTOR_XPATH);
        $row->find($this->editButton)->click();
    }

    /**
     * Returns product qty
     *
     * @param string $productToUpdate
     * @return int
     */
    public function getQty($productToUpdate)
    {
        $row = $this->_rootElement->find(sprintf($this->itemBox, $productToUpdate), Locator::SELECTOR_XPATH);
        return $row->find($this->qtyInput)->getValue();
    }

    /**
     * Returns product options values
     *
     * @param string $productToUpdate
     * @return array
     */
    public function getOptionValues($productToUpdate)
    {
        $row = $this->_rootElement->find(sprintf($this->itemBox, $productToUpdate), Locator::SELECTOR_XPATH);
        $optionValues = $row->getElements($this->optionValues);
        $options = [];
        foreach ($optionValues as $optionValue) {
            $options[] = $optionValue->getText();
        }
        return $options;
    }

    /**
     * Verifies that both including and excluding tax prices are visible
     *
     * @return bool
     */
    public function arePricesVisible()
    {
        $rows = $this->_rootElement->getElements($this->itemSelector);
        $result = true;
        foreach ($rows as $row) {
            if (!$row->find($this->price)->isVisible() || !$row->find($this->priceExclTax)->isVisible()) {
                $result = false;
                break;
            }
        }
        return $result;
    }

    /**
     * Click print link
     *
     * @return void
     */
    public function clickPrint()
    {
        $this->_rootElement->find($this->printLink)->click();
    }

    /**
     * Select all products in the requisition list
     *
     * @return void
     */
    public function selectProducts()
    {
        $this->_rootElement->find($this->selectAll)->click();
    }

    /**
     * Perform action on requisition list
     *
     * @param string $action
     * @return void
     */
    public function performAction($action)
    {
        $this->_rootElement->find(sprintf($this->actionButton, $action), Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find(sprintf($this->createNew, $action), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Returns qty array
     *
     * @return array
     */
    public function getQtys()
    {
        $qtyArray = [];
        $rows = $this->_rootElement->getElements($this->itemSelector);
        foreach ($rows as $row) {
            $qtyArray[$row->find($this->sku)->getText()] = (int) $row->find($this->qtyInput)->getValue();
        }

        return $qtyArray;
    }

    /**
     * Add selected products to cart
     *
     * @return void
     */
    public function addProductsToCart()
    {
        $this->_rootElement->find($this->addToCartButton)->click();
    }
}
