<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Mtf\Client\ElementInterface;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

/**
 * Second step - pricing grid of shared catalog configuration.
 */
class PricingGrid extends DataGrid
{
    /**
     * Css selector Custom Price input.
     *
     * @var string
     */
    private $inputLocator = '.currency-addon input';

    /**
     * Css selector Customer Price type.
     *
     * @var string
     */
    private $customPriceType = '.admin__field-control select';

    /**
     * Css selector checkbox.
     *
     * @var string
     */
    private $checkboxLocator = '.data-grid-checkbox-cell .data-grid-checkbox-cell-inner';

    /**
     * Set Discount mass action option text.
     *
     * @var string
     */
    private $actionTypeSetDiscount = 'Set Discount';

    /**
     * Adjust Fixed Price mass action option text.
     *
     * @var string
     */
    private $actionTypeAdjustFixedPrice = 'Adjust Fixed Price';

    /**
     * Xpath locator Configure tier price link.
     *
     * @var string
     */
    private $configurePriceField = '//tr//td[contains(@class, \'configure-column-field-tier-price\')]//a';

    /**
     * Css selector website filter.
     *
     * @var string
     */
    private $websiteFilter = '.admin__data-website-switcher .switcher-dropdown';

    /**
     * Xpath locator website filter option.
     *
     * @var string
     */
    private $websiteFilterOption =
        '//div[@class="admin__data-website-switcher"]/div/ul/li/span[contains(text(), "%s")]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '[name="sku"]',
        ],
    ];

    /**
     * Css selector for advanced pricing grid loader.
     *
     * @var string
     */
    protected $advancedPricingLoader = '.admin__form-loading-mask';

    /**
     * Open apply discount popup.
     *
     * @return void
     */
    public function applyDiscount()
    {
        $this->openMassActionPopup($this->actionTypeSetDiscount);
    }

    /**
     * Open adjust fixed price popup.
     *
     * @return void
     */
    public function adjustFixedPrice()
    {
        $this->openMassActionPopup($this->actionTypeAdjustFixedPrice);
    }

    /**
     * Is configure tier price link visible.
     *
     * @return ElementInterface
     */
    public function canConfigurePrice()
    {
        return $this->_rootElement->find($this->configurePriceField, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Open tier price configuration slideout.
     *
     * @return void
     */
    public function openTierPriceConfiguration()
    {
        $this->_rootElement->find($this->configurePriceField, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->advancedPricingLoader);
    }

    /**
     * Get input value.
     *
     * @return string
     */
    public function retrieveInputValue()
    {
        $this->waitLoader();
        $this->getTemplateBlock()->waitForElementNotVisible($this->loader);
        return $this->_rootElement->find($this->inputLocator)->getValue();
    }

    /**
     * Filter products by website.
     *
     * @param string $websiteName
     * @return void
     */
    public function filterProductsByWebsite($websiteName)
    {
        $this->waitLoader();
        $this->_rootElement->find($this->websiteFilter)->click();
        $this->_rootElement->find(sprintf($this->websiteFilterOption, $websiteName), Locator::SELECTOR_XPATH)->click();
        $this->waitLoader();
    }

    /**
     * Set custom price value.
     *
     * @param float $priceValue
     * @return void
     */
    public function setCustomPrice($priceValue)
    {
        $this->waitLoader();
        $this->_rootElement->find($this->inputLocator)->setValue($priceValue);
        $this->focusOutFromCustomPriceInput();
        $this->waitLoader();
    }

    /**
     * Search product by $filter and set custom price value.
     *
     * @param array $filter
     * @param float $priceValue
     * @return void
     */
    public function setCustomPriceByFilter(array $filter, $priceValue)
    {
        $this->waitForLoader();
        $row = $this->getRow($filter);
        $row->find($this->inputLocator)->setValue($priceValue);
        $this->focusOutFromCustomPriceInput();
    }

    /**
     * Select item by $filter for mass action.
     *
     * @param array $filter
     * @return void
     */
    public function selectItem(array $filter)
    {
        $this->waitLoader();
        $row = $this->getRow($filter);
        $checkbox = $row->find($this->checkboxLocator, Locator::SELECTOR_CSS, 'checkbox');
        $checkbox->setValue('Yes');
    }

    /**
     * Set custom price type.
     *
     * @param string $customPriceType
     * @return void
     */
    public function setCustomPriceType($customPriceType)
    {
        $this->_rootElement->find($this->customPriceType, Locator::SELECTOR_CSS, 'select')->setValue($customPriceType);
    }

    /**
     * Is custom price type select disabled.
     *
     * @return bool
     */
    public function isCustomPriceTypeSelectDisabled()
    {
        $disabledAttributeValue = $this->_rootElement->find($this->customPriceType)->getAttribute('disabled');

        return $disabledAttributeValue === 'true';
    }

    /**
     * Is custom price input disabled.
     *
     * @return bool
     */
    public function isCustomPriceInputDisabled()
    {
        $disabledAttributeValue = $this->_rootElement->find($this->inputLocator)->getAttribute('disabled');

        return $disabledAttributeValue === 'true';
    }

    /**
     * Wait for loader.
     *
     * @return void
     */
    public function waitForLoader()
    {
        $this->waitForElementNotVisible(
            '#catalog-steps-wizard_step_pricing .configure-step-right .admin__data-grid-loading-mask',
            Locator::SELECTOR_CSS
        );
    }

    /**
     * Open Set Discount or Adjust Fixed Price popup.
     *
     * @param string $actionType
     * @return void
     */
    private function openMassActionPopup($actionType)
    {
        $this->waitLoader();
        $this->waitForElementVisible($this->selectItem);
        $rowsCheckboxes = $this->_rootElement->getElements($this->selectItem);
        foreach ($rowsCheckboxes as $selectItem) {
            $selectItem->click();
        }

        $this->selectAction($actionType);
    }

    /**
     * Focus out from custom price input for saving custom price.
     *
     * @return void
     */
    private function focusOutFromCustomPriceInput()
    {
        $this->_rootElement->find($this->customPriceType)->click();
    }
}
