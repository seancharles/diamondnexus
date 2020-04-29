<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Quote details block.
 *
 * @SuppressWarnings(PHPMD)
 */
class QuoteDetails extends Block
{
    /**
     * CSS locator for sku.
     *
     * @var string
     */
    private $sku = '.product-sku-block';

    /**
     * CSS locator for expiration date field.
     *
     * @var string
     */
    private $expirationDateField = '#expiration_date';

    /**
     * CSS locator for proposed shipping price field.
     *
     * @var string
     */
    private $proposedShippingPriceField = '#quote-shipping-price-input';

    /**
     * CSS locator for amount discount field.
     *
     * @var string
     */
    private $amountDiscountField = '.amount td input';

    /**
     * CSS locator for amount discount radio button.
     *
     * @var string
     */
    private $amountDiscountRadioButton = '.amount th input';

    /**
     * CSS locator for percentage discount field.
     *
     * @var string
     */
    private $percentageDiscountField = '.percentage td input';

    /**
     * CSS locator for percentage discount radio button.
     *
     * @var string
     */
    private $percentageDiscountRadioButton = '.percentage th input';

    /**
     * CSS locator for proposed price field.
     *
     * @var string
     */
    private $proposedPriceField = '.proposed td input';

    /**
     * CSS locator for proposed price radio button.
     *
     * @var string
     */
    private $proposedPriceRadioButton = '.proposed th input';

    /**
     * Css locator for qty inputs.
     *
     * @var string
     */
    private $qty = 'input.item-qty';

    /**
     * Css locator for update buttons.
     *
     * @var string
     */
    private $updateButton = '.update-button';

    /**
     * CSS locator for quote name.
     *
     * @var string
     */
    private $quoteName = '#quote_name';

    /**
     * CSS locator for quote status.
     *
     * @var string
     */
    private $quoteStatus = '#quote_status';

    /**
     * CSS locator for lock message.
     *
     * @var string
     */
    private $lock = '[data-ui-id="messages-message-notice"]';

    /**
     * Css locator for loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Totals rows css selector.
     *
     * @var string
     */
    private $totals = '.quote-subtotal-table>tbody>tr:not(.catalog_price_table)';

    /**
     * Totals row price css selector.
     *
     * @var string
     */
    private $totalPrice = '.price';

    /**
     * Shipping address css selector.
     *
     * @var string
     */
    private $shippingMethod = '.admin__quote-shipment-methods-title';

    /**
     * Get methods link.
     *
     * @var string
     */
    private $getMethods = '.get-shipping-method-link';

    /**
     * Flat rate input css selector.
     *
     * @var string
     */
    private $flatRate = '#s_method_flatrate_flatrate';

    /**
     * Comments selector.
     *
     * @var string
     */
    private $comments = '.comments-block-item-comment';

    /**
     * Quote comment field.
     *
     * @var string
     */
    private $commentField = '#negotiation_comment';

    /**
     * Hsitory log tab css locator.
     *
     * @var string
     */
    private $historyLogTab = '#grid_tab_new_history';

    /**
     * Block with history log.
     *
     * @var string
     */
    private $historyLog = '.history-log-block-item-title';

    /**
     * Product added selector.
     *
     * @var string
     */
    private $productAdded = '.history-log-product-added';

    /**
     * Product updated selector.
     *
     * @var string
     */
    private $productUpdated = '.history-log-product-updated';

    /**
     * "Add Products By SKU" button selector.
     *
     * @var string
     */
    private $addProductsBySkuButton = '#show-sku-form';

    /**
     * Product SKU input selector.
     *
     * @var string
     */
    private $skuInput = '.block-addbysku .col-sku input';

    /**
     * Product qty input selector.
     *
     * @var string
     */
    private $qtyInput = '.block-addbysku .col-qty input';

    /**
     * "Add Products By SKU" button selector.
     *
     * @var string
     */
    private $addToQuoteButton = '.actions .action-add.action-secondary';

    /**
     * "Configure" button selector.
     *
     * @var string
     */
    private $configureButton = '.sku-configure-button button:not(.action-disabled)';

    /**
     * Modals overlay.
     *
     * @var string
     */
    private $modalsOverlay = '.modals-overlay';

    /**
     * Add products to quote button selector.
     *
     * @var string
     */
    private $addProductsToQuoteButton = '.actions .action-default.action-add';

    /**
     * Remove product button selector.
     *
     * @var string
     */
    private $removeProductsFromQuoteButton = '.col-remove .action-default.delete';

    /**
     * Selector for block with items to add.
     *
     * @var string
     */
    private $itemsErrorsBlock = '#itemsErrors';

    /**
     * Page header selector.
     *
     * @var string
     */
    private $header = 'header.page-header';

    /**
     * Xpath selector for updated product name.
     *
     * @var string
     */
    private $updatedProductName = '//div[@class="sku-configure-button"]/parent::div/parent::td/span';

    /**
     * Xpath selector for added product name.
     *
     * @var string
     */
    private $addedProductName = '//div[@class="sku-configure-button"]/parent::div/p[1]';

    /**
     * CSS selector for update prices button.
     *
     * @var string
     */
    private $updatePricesButton = '.update-order-prices-button';

    /**
     * CSS selector for recalculate quote button.
     *
     * @var string
     */
    private $recalculateQuoteButton = '.update-button';

    /**
     * Css selector shipping and handling price.
     *
     * @var string
     */
    private $shippingAndHandlingPrice = '.quote-totals [data-role=\'shipping-price-wrap\'] .price';

    /**
     * Css selector quote items tax label.
     *
     * @var string
     */
    private $quoteItemsTaxLabel = '.data-table.admin__table-primary.order-tables thead tr .col-row-tax-amount span';

    /**
     * Css selector quote totals tax label.
     *
     * @var string
     */
    private $quoteTotalsTaxLabel = '.data-table.admin__table-secondary.quote-subtotal-table .quote_tax th';

    /**
     * Css selector totals subtotal tax label.
     *
     * @var string
     */
    private $quoteTotalsSubtotalTaxLabel = '#toggle-part .data-table .tax th';

    /**
     * Css selector quote subtotal toggle control.
     *
     * @var string
     */
    private $quoteSubtotalToggleControl = '.data-table.toggle-action tbody tr th .toggle';

    /**
     * Css selector for Created By field.
     *
     * @var string
     */
    private $createdBy = '#quote_created_by';

    /**
     * Css selector for Company Name field.
     *
     * @var string
     */
    private $companyName = '#quote_company';

    /**
     * Css selector for Company Admin Email field.
     *
     * @var string
     */
    private $companyAdminEmail = '#quote_mailto';

    /**
     * Xpath locator for table column containing a quote item.
     *
     * @var string
     */
    private $quoteItemSkuTableColumn = 'parent::td[@class="col-product"]';

    /**
     * Css selector for quote item options.
     *
     * @var string
     */
    private $quoteItemOptions = '.item-options';

    /**
     * Get comments.
     *
     * @return array
     */
    public function getComments()
    {
        $comments = [];
        $rows = $this->_rootElement->getElements($this->comments);

        foreach ($rows as $row) {
            $comments[] = trim($row->getText());
        }

        return $comments;
    }

    /**
     * Open history log tab.
     *
     * @return void
     */
    public function openHistoryLogTab()
    {
        $this->_rootElement->find($this->historyLogTab)->click();
        $this->waitForElementVisible($this->historyLog);
    }

    /**
     * Get history log.
     *
     * @return array
     */
    public function getHistoryLog()
    {
        $log = [];
        $rows = $this->_rootElement->getElements($this->historyLog);

        foreach ($rows as $row) {
            $log[] = trim($row->getText());
        }

        return $log;
    }

    /**
     * Returns array of quote totals.
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            if (count($row->getElements($this->totalPrice))) {
                $totals[$row->getAttribute('class')] = $row->find($this->totalPrice)->getText();
            }
        }

        return $totals;
    }

    /**
     * Checks if method is correct.
     *
     * @param string $method
     * @return bool
     */
    public function isMethodCorrect($method)
    {
        if (!$method) {
            return true;
        }
        $text = trim($this->_rootElement->find($this->shippingMethod)->getText());
        return strpos($text, $method) !== false;
    }

    /**
     * Gets SKU list.
     *
     * @return array
     */
    public function getSkuList()
    {
        $skuArr = [];
        $this->waitForElementVisible($this->sku);
        $elements = $this->_rootElement->getElements($this->sku);
        foreach ($elements as $element) {
            $skuArr[] = str_replace(['SKU: ', PHP_EOL, 'Configure'], '', $element->getText());
        }

        return $skuArr;
    }

    /**
     * Get SKU list for a complex products.
     *
     * @return array
     */
    public function getComplexProductsSkuList()
    {
        $skuArr = [];
        $this->waitForElementVisible($this->sku);
        $elements = $this->_rootElement->getElements($this->sku);

        foreach ($elements as $element) {
            $elementContainer = $element->find($this->quoteItemSkuTableColumn, Locator::SELECTOR_XPATH);

            if ($elementContainer->find($this->configureButton)->isVisible()) {
                $configurationInfo = str_replace(['SKU: ', PHP_EOL, 'Configure'], '', $element->getText());
                $quoteItemOptions = $element->find($this->quoteItemOptions);

                if ($quoteItemOptions->isVisible()) {
                    $optionsInfo = str_replace(PHP_EOL, '', $quoteItemOptions->getText());
                    $configurationInfo = str_replace($optionsInfo, '', $configurationInfo);
                }

                $skuArr[] = $configurationInfo;
            }
        }

        return $skuArr;
    }

    /**
     * Get expiration date.
     *
     * @return string
     */
    public function getExpirationDate()
    {
        return trim($this->_rootElement->find($this->expirationDateField)->getValue());
    }

    /**
     * Fill expiration date.
     *
     * @param \DateTime $date
     * @return void
     */
    public function fillExpirationDate(\DateTime $date)
    {
        $this->_rootElement->find($this->expirationDateField)->setValue($date->format('M d, Y'));
    }

    /**
     * Fill ProposedShippingPrice.
     *
     * @param string $proposedShippingPrice
     * @return void
     */
    public function fillProposedShippingPrice($proposedShippingPrice)
    {
        $this->browser->find($this->getMethods)->click();
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->flatRate)->click();
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->proposedShippingPriceField)->setValue($proposedShippingPrice);
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Get proposed shipping.
     *
     * @return string
     */
    public function getProposedShippingPrice()
    {
        return trim($this->_rootElement->find($this->proposedShippingPriceField)->getValue());
    }

    /**
     * Update items.
     *
     * @param array $qtys
     * @return void
     */
    public function updateItems(array $qtys)
    {
        $elements = $this->_rootElement->getElements($this->qty);
        $i = 0;
        foreach ($elements as $element) {
            $element->setValue($qtys[$i]);
            $i++;
        }
        $this->_rootElement->find($this->updateButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Get qty list.
     *
     * @return array
     */
    public function getQtyList()
    {
        $qtyArr = [];
        $elements = $this->_rootElement->getElements($this->qty);

        foreach ($elements as $element) {
            $qtyArr[] = $element->getValue();
        }

        return $qtyArr;
    }

    /**
     * Gets quote name.
     *
     * @return string
     */
    public function getQuoteName()
    {
        return trim($this->_rootElement->find($this->quoteName)->getText());
    }

    /**
     * Gets quote status.
     *
     * @return string
     */
    public function getQuoteStatus()
    {
        return trim($this->_rootElement->find($this->quoteStatus)->getText());
    }

    /**
     * Gets lock.
     *
     * @return string
     */
    public function isLock()
    {
        return (bool) $this->_rootElement->find($this->lock)->isVisible();
    }

    /**
     * Update comment.
     *
     * @param string $message
     * @return void
     */
    public function updateComment($message)
    {
        $this->_rootElement->find($this->commentField)->setValue($message);
    }

    /**
     * Fill discount.
     *
     * @param string $discountType
     * @param int $tax
     * @return void
     */
    public function fillDiscount($discountType, $tax)
    {
        switch ($discountType) {
            case 'amount':
                $this->_rootElement->find($this->amountDiscountRadioButton)->click();
                $this->_rootElement->find($this->amountDiscountField)->setValue($tax);
                break;
            case 'percentage':
                $this->_rootElement->find($this->percentageDiscountRadioButton)->click();
                $this->_rootElement->find($this->percentageDiscountField)->setValue($tax);
                break;
            case 'proposed':
                $this->_rootElement->find($this->proposedPriceRadioButton)->click();
                $this->_rootElement->find($this->proposedPriceField)->setValue($tax);
                break;
        }
        $this->_rootElement->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Add products by SKU.
     *
     * @param array $skuArray
     * @return void
     */
    public function addProductsBySku(array $skuArray)
    {
        foreach ($skuArray as $sku) {
            $this->browser->find($this->header)->hover();
            $this->_rootElement->find($this->addProductsBySkuButton)->click();
            $this->_rootElement->find($this->skuInput)->setValue($sku);
            $this->_rootElement->find($this->qtyInput)->setValue(5);
            $this->_rootElement->find($this->skuInput)->click();
            $this->browser->find($this->header)->hover();
            $this->waitForElementNotDisabled($this->addToQuoteButton);
            $this->_rootElement->find($this->addToQuoteButton)->click();
            $this->waitForElementNotVisible($this->loader);
        }
    }

    /**
     * Wait for element is not disabled in the block.
     *
     * @param string $selector
     * @param string $strategy [optional]
     * @return bool|null
     */
    public function waitForElementNotDisabled($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $element = $browser->find($selector, $strategy);
                return $element->isDisabled() == false ? true : null;
            }
        );
    }

    /**
     * Click configure button.
     *
     * @return void
     */
    public function clickConfigureButton()
    {
        $this->waitForElementNotVisible($this->modalsOverlay);
        $this->_rootElement->find($this->configureButton)->click();
    }

    /**
     * Click update button.
     *
     * @return void
     */
    public function clickUpdateButton()
    {
        $this->waitForElementNotVisible($this->modalsOverlay);
        $this->_rootElement->find($this->updateButton)->click();
    }

    /**
     * Click add products to quote button.
     *
     * @return void
     */
    public function clickAddProductsToQuote()
    {
        $this->waitForElementNotVisible($this->modalsOverlay);
        $this->browser->find($this->header)->hover();
        $this->_rootElement->find($this->addProductsToQuoteButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Remove failed products from quote.
     *
     * @return void
     */
    public function removeProducts()
    {
        $rows = $this->_rootElement->getElements($this->removeProductsFromQuoteButton);
        foreach ($rows as $row) {
            $row->click();
            $this->waitForElementNotVisible($this->loader);
        }
    }

    /**
     * Verify whether block with items to add is visible.
     *
     * @return bool
     */
    public function isItemsBlockVisible()
    {
        return (bool) $this->_rootElement->find($this->itemsErrorsBlock)->isVisible();
    }

    /**
     * Get added products log.
     *
     * @return array
     */
    public function getAddedProductsLog()
    {
        $log = [];
        $rows = $this->_rootElement->getElements($this->productAdded);

        foreach ($rows as $row) {
            $log[] = trim($row->getText());
        }

        return $log;
    }

    /**
     * Get updated products log.
     *
     * @return array
     */
    public function getUpdatedProductsLog()
    {
        $log = [];
        $rows = $this->_rootElement->getElements($this->productUpdated);

        foreach ($rows as $row) {
            $log[] = trim($row->getText());
        }

        return $log;
    }

    /**
     * Get updated product name.
     *
     * @return string
     */
    public function getUpdatedProductName()
    {
        return $this->_rootElement->find($this->updatedProductName, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get added product name.
     *
     * @return string
     */
    public function getAddedProductName()
    {
        return $this->_rootElement->find($this->addedProductName, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Click update prices button.
     *
     * @return void
     */
    public function updatePrices()
    {
        $this->_rootElement->find($this->updatePricesButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click recalculate quote button.
     *
     * @return void
     */
    public function recalculateQuote()
    {
        $this->_rootElement->find($this->recalculateQuoteButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Get shipping and handling price.
     *
     * @return string
     */
    public function getShippingAndHandlingPrice()
    {
        return trim($this->_rootElement->find($this->shippingAndHandlingPrice)->getText());
    }

    /**
     * Get quote items tax label.
     *
     * @return string
     */
    public function getQuoteItemsTaxLabel()
    {
        return trim($this->_rootElement->find($this->quoteItemsTaxLabel)->getText());
    }

    /**
     * Get quote totals tax label.
     *
     * @return string
     */
    public function getQuoteTotalsTaxLabel()
    {
        return trim($this->_rootElement->find($this->quoteTotalsTaxLabel)->getText());
    }

    /**
     * Get quote totals subtotal tax label.
     *
     * @return string
     */
    public function getQuoteTotalsSubtotalTaxLabel()
    {
        $this->_rootElement->find($this->quoteSubtotalToggleControl)->click();

        return trim($this->_rootElement->find($this->quoteTotalsSubtotalTaxLabel)->getText());
    }

    /**
     * Get Created By field value.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return trim($this->_rootElement->find($this->createdBy)->getText());
    }

    /**
     * Get Company Name field value.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return trim($this->_rootElement->find($this->companyName)->getText());
    }

    /**
     * Get Company Admin Email field value.
     *
     * @return string
     */
    public function getCompanyAdminEmail()
    {
        return trim($this->_rootElement->find($this->companyAdminEmail)->getText());
    }
}
