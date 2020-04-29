<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;
use Magento\Mtf\Client\Locator;

/**
 * Shared catalog tier price modal block.
 */
class TierPriceModal extends AbstractOptions
{
    /**
     * Css selector close button.
     *
     * @var string
     */
    private $closeButton = 'button.action-close';

    /**
     * Mapping mode.
     *
     * @var bool
     */
    protected $mappingMode = true;

    /**
     * Css selector row element.
     *
     * @var string
     */
    private $rowSelector = '//tr[.//input[contains(@name, "[qty]")]]';

    /**
     * Css selector remove price button.
     *
     * @var string
     */
    private $deleteButton = '[data-action="remove_row"]';

    /**
     * Css selector save button.
     *
     * @var string
     */
    private $doneButton = '[data-ui-id="done-button"]';

    /**
     * Css selector Add New Row button.
     *
     * @var string
     */
    private $addNewRowButton = '[data-action="add_new_row"]';

    /**
     * Css selector tier price qty input.
     *
     * @var string
     */
    private $tierPriceQtyInput = '[name="tier_price[%s][qty]"]';

    /**
     * Css selector tier price price input.
     *
     * @var string
     */
    private $tierPricePriceInput = '[name="tier_price[%s][price]"]';

    /**
     * Css selector tier price option.
     *
     * @var string
     */
    private $tierPriceOption = '[data-index="tier_price"] tbody tr';

    /**
     * Xpath selector for website id.
     *
     * @var string
     */
    private $websiteSelector = '//select[contains(@name, "[website_id]")]';

    /**
     * Xpath selector for quantity field.
     *
     * @var string
     */
    private $qtySelector = '//input[contains(@name, "[qty]")]';

    /**
     * Css selector for currency symbol.
     *
     * @var string
     */
    private $currencySymbol = '//label[@class="admin__addon-prefix"]/span';

    /**
     * Close tier price modal.
     *
     * @return void
     */
    public function close()
    {
        $this->_rootElement->find($this->closeButton)->click();
    }

    /**
     * Get row data by qty.
     *
     * @param int $qty
     * @return array
     */
    public function getRowDataByQty($qty)
    {
        $data = null;
        $this->waitForElementVisible($this->rowSelector, Locator::SELECTOR_XPATH);
        $rowElements = $this->_rootElement->getElements($this->rowSelector, Locator::SELECTOR_XPATH);
        foreach ($rowElements as $element) {
            $data = $this->getDataOptions(null, $element);
            if ($data['qty'] == $qty) {
                return $data;
            }
        }
        return $data;
    }

    /**
     * Delete all tier prices.
     *
     * @return void
     */
    public function deleteAllTierPrices()
    {
        $this->waitForElementVisible($this->rowSelector, Locator::SELECTOR_XPATH);
        $elements = $this->_rootElement->getElements($this->deleteButton);

        foreach ($elements as $element) {
            $element->click();
        }
    }

    /**
     * Add shared catalog product tier prices.
     *
     * @param array $tierPricesData
     * @return void
     */
    public function addTierPrices(array $tierPricesData)
    {
        $rowNumber = 0;

        foreach ($tierPricesData as $tierPriceData) {
            $this->_rootElement->find($this->addNewRowButton)->click();
            $this->_rootElement->find(sprintf($this->tierPriceQtyInput, $rowNumber))->setValue($tierPriceData['qty']);
            $this->_rootElement->find(sprintf($this->tierPricePriceInput, $rowNumber))
                ->setValue($tierPriceData['price']);
            $rowNumber++;
        }
    }

    /**
     * Save advanced pricing data.
     *
     * @return void
     */
    public function save()
    {
        $this->_rootElement->find($this->doneButton)->click();
    }

    /**
     * Is tier price options present in advanced price settings section.
     *
     * @return bool
     */
    public function isTierPriceOptionsPresent()
    {
        $options = $this->_rootElement->getElements($this->tierPriceOption);

        return (bool)count($options);
    }

    /**
     * Get tier price currency symbols array by qty.
     *
     * @param int $qty
     * @return array
     */
    public function getPriceCurrencySymbol($qty)
    {
        $tierPriceData = [];
        $this->waitForElementVisible($this->rowSelector, Locator::SELECTOR_XPATH);
        $rowElements = $this->_rootElement->getElements($this->rowSelector, Locator::SELECTOR_XPATH);
        foreach ($rowElements as $element) {
            if ($element->find($this->qtySelector, Locator::SELECTOR_XPATH)->getValue() == $qty &&
                $element->find($this->currencySymbol, Locator::SELECTOR_XPATH)->isVisible()) {
                $tierPriceData[$element->find($this->websiteSelector, Locator::SELECTOR_XPATH)->getValue()] =
                    $element->find($this->currencySymbol, Locator::SELECTOR_XPATH)->getText();
            }
        }
        return $tierPriceData;
    }

    /**
     * Switch website scope in tier prices modal.
     *
     * @param string $websiteName
     * @return void
     */
    public function switchScope($websiteName)
    {
        $this->_rootElement
            ->find($this->websiteSelector, Locator::SELECTOR_XPATH, 'selectstore')
            ->setValue($websiteName);
    }
}
