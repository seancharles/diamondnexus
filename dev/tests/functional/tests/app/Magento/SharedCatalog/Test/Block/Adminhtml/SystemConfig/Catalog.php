<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\SystemConfig;

use Magento\Mtf\Block\Block;

/**
 * Block for system configuration Catalog section.
 */
class Catalog extends Block
{
    /**
     * Css selector Category Permissions section anchor.
     *
     * @var string
     */
    private $categoryPermissionsAnchor = '#catalog_magento_catalogpermissions-head';

    /**
     * Css selector for Category Permissions Enable select.
     *
     * @var string
     */
    private $categoryPermissionsEnableSelect = '#catalog_magento_catalogpermissions_enabled';

    /**
     * Css selector for Enable selected option.
     *
     * @var string
     */
    private $categoryPermissionsEnableSelectedOption =
        '#catalog_magento_catalogpermissions_enabled option[selected="selected"]';

    /**
     * Css selector for Allow Browsing Category selected option.
     *
     * @var string
     */
    private $allowBrowsingCategorySelectedOption =
        '#catalog_magento_catalogpermissions_grant_catalog_category_view option[selected="selected"]';

    /**
     * Css selector for Display Product Prices selected option.
     *
     * @var string
     */
    private $displayProductPricesSelectedOption =
        '#catalog_magento_catalogpermissions_grant_catalog_product_price option[selected="selected"]';

    /**
     * Css selector for Allow Adding to Cart selected option.
     *
     * @var string
     */
    private $allowAddingToCartSelectedOption =
        '#catalog_magento_catalogpermissions_grant_checkout_items option[selected="selected"]';

    /**
     * Open system configuration Catalog Category Permissions section.
     *
     * @return void
     */
    public function openCategoryPermissionsSection()
    {
        $openedClass = $this->_rootElement->find($this->categoryPermissionsAnchor)->getAttribute('class');

        if ($openedClass != 'open') {
            $this->_rootElement->find($this->categoryPermissionsAnchor)->click();
        }
    }

    /**
     * Checks is Category Permissions Enable control disabled.
     *
     * @return bool
     */
    public function isCategoryPermissionsEnableControlDisabled()
    {
        $disabledAttribute = $this->_rootElement->find($this->categoryPermissionsEnableSelect)
            ->getAttribute('disabled');

        return (bool)$disabledAttribute;
    }

    /**
     * Get Category Permissions Enable selected option text.
     *
     * @return string
     */
    public function getCategoryPermissionsEnableValue()
    {
        return trim($this->_rootElement->find($this->categoryPermissionsEnableSelectedOption)->getText());
    }

    /**
     * Get Category Permissions Allow Browsing Category selected option text.
     *
     * @return string
     */
    public function getCategoryPermissionsAllowBrowsingCategoryValue()
    {
        return trim($this->_rootElement->find($this->allowBrowsingCategorySelectedOption)->getText());
    }

    /**
     * Get Category Permissions Display Product Prices selected option text.
     *
     * @return string
     */
    public function getCategoryPermissionsDisplayProductPricesValue()
    {
        return trim($this->_rootElement->find($this->displayProductPricesSelectedOption)->getText());
    }

    /**
     * Get Category Permissions Allow Adding to Cart selected option text.
     *
     * @return string
     */
    public function getCategoryPermissionsAllowAddingToCartValue()
    {
        return trim($this->_rootElement->find($this->allowAddingToCartSelectedOption)->getText());
    }
}
