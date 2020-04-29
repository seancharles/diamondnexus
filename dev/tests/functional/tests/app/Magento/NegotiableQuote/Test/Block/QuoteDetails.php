<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Quote Details block
 *
 * @SuppressWarnings(PHPMD)
 */
class QuoteDetails extends Block
{
    /**
     * Disabled buttons css selectors.
     *
     * @var array
     */
    protected $disabledButtons = [
        'checkout' => '.action.checkout._disabled',
        'send' => '.action.send._disabled',
        'delete' => '.action.delete._disabled',
    ];

    /**
     * CSS locator for sku.
     *
     * @var string
     */
    protected $sku = 'td.col.sku';

    /**
     * CSS locator for qty.
     *
     * @var string
     */
    private $qty = 'div.control.qty input';

    /**
     * CSS locator for price.
     *
     * @var string
     */
    protected $price = 'td.col.price';

    /**
     * CSS locator for expiration date.
     *
     * @var string
     */
    protected $expirationDate = '.quote-date-expired date';

    /**
     * CSS locator for created field.
     *
     * @var string
     */
    protected $created = '.quote-date-created';

    /**
     * CSS locator for quote name.
     *
     * @var string
     */
    protected $quoteName = '.quote-name';

    /**
     * CSS locator for status.
     *
     * @var string
     */
    protected $status = '.quote-status';

    /**
     * CSS locator for lock message.
     *
     * @var string
     */
    protected $lock = '.message-notice';

    /**
     * Update button css selector.
     *
     * @var string
     */
    protected $updateButton = '.action.update';

    /**
     * Send button css selector.
     *
     * @var string
     */
    protected $sendButton = '.action.send';

    /**
     * Close button css selector.
     *
     * @var string
     */
    protected $closeButton = '.action.close';

    /**
     * Delete button css selector.
     *
     * @var string
     */
    protected $deleteButton = '.action.delete';

    /**
     * Shipping address css selector.
     *
     * @var string
     */
    protected $shippingMethod = '.box-order-shipping-method .box-content';

    /**
     * OK button css selector.
     *
     * @var string
     */
    protected $okButton = '.confirm._show .action-primary.action-accept';

    /**
     * Totals rows css selector.
     *
     * @var string
     */
    protected $totals = '#shopping-cart-table-totals>tfoot>tr:not(.catalog_price_table)';

    /**
     * Totals row price css selector.
     *
     * @var string
     */
    protected $totalPrice = 'td .price';

    /**
     * Edit address link css selector.
     *
     * @var string
     */
    protected $editAddress = '.action.edit';

    /**
     * Add new address link css selector.
     *
     * @var string
     */
    protected $addNewAddress = '//div[@class="box-actions"]/a/span[contains(., "Add New Address")]';

    /**
     * Get shipping address.
     *
     * @var string
     */
    protected $shippingAddressBox = '.box-shipping-address .box-content';

    /**
     * Block with comments.
     *
     * @var string
     */
    protected $comments = '.comments-block-item-comment';

    /**
     * Block with history log.
     *
     * @var string
     */
    protected $historyLog = '.history-log-block-item-title';

    /**
     * Product added selector.
     *
     * @var string
     */
    protected $productAdded = '.history-log-product-added';

    /**
     * Product updated selector.
     *
     * @var string
     */
    protected $productUpdated = '.history-log-product-updated';

    /**
     * Comments tab css locator.
     *
     * @var string
     */
    protected $commentsTab = '#tab-label-comments-title';

    /**
     * Hsitory log tab css locator.
     *
     * @var string
     */
    protected $historyLogTab = '#tab-label-history-log-title';

    /**
     * Comments tab container.
     *
     * @var string
     */
    protected $commentsTabContainer = '#comments';

    /**
     * Negotiable quote comment field.
     *
     * @var string
     */
    protected $commentField = '#negotiation_comment';

    /**
     * Go To Checkout button css selector.
     *
     * @var string
     */
    protected $checkoutButton = '.action.checkout';

    /**
     * Print link css selector.
     *
     * @var string
     */
    protected $printLink = '.action.print';

    /**
     * Proposed shipping price css selector.
     *
     * @var string
     */
    protected $proposedShippingPrice = '.proposed_shipping .price';

    /**
     * Css selector quote totals tax label.
     *
     * @var string
     */
    protected $quoteTotalsTaxLabel = '#shopping-cart-table-totals .quote_tax th';

    /**
     * Css selector quote totals subtotal tax label.
     *
     * @var string
     */
    protected $quoteTotalsSubtotalTaxLabel = '#shopping-cart-table-totals .data-table .tax th';

    /**
     * Css selector quote subtotal toggle control.
     *
     * @var string
     */
    protected $quoteSubtotalToggleControl = '.data-table.toggle-action .catalog_price th span';

    /**
     * Css locator for spinner.
     *
     * @var string
     */
    private $spinner = '.spinner';

    /**
     * Click edit address link.
     *
     * @return void
     */
    public function clickEditAddress()
    {
        $this->_rootElement->find($this->editAddress)->click();
    }

    /**
     * Click add new address link.
     *
     * @return void
     */
    public function clickNewAddress()
    {
        $this->_rootElement->find($this->addNewAddress, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Return shipping address.
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->_rootElement->find($this->shippingAddressBox)->getText();
    }

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
            $key = explode(' ', $row->getAttribute('class'))[0];
            $totals[$key] = $row->find($this->totalPrice)->getText();
        }

        return $totals;
    }

    /**
     * Checks if method is correct.
     *
     * @param $method
     * @return bool
     */
    public function isMethodCorrect($method)
    {
        $text = trim($this->_rootElement->find($this->shippingMethod)->getText());
        if (!$method) {
            return $text == 'No shipping information available';
        }
        return strpos($text, $method) !== false;
    }

    /**
     * Checks if buttons are disabled.
     *
     * @param array $buttons
     * @return bool
     */
    public function areButtonsDisabled(array $buttons)
    {
        $result = true;
        foreach ($buttons as $button) {
            $result = (bool)count($this->_rootElement->getElements($this->disabledButtons[$button]));
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Gets lock.
     *
     * @return bool
     */
    public function isLock()
    {
        return (bool)count($this->_rootElement->getElements($this->lock));
    }

    /**
     * Gets status.
     *
     * @return string
     */
    public function getStatus()
    {
        return trim($this->_rootElement->find($this->status)->getText());
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
     * Gets expiration date.
     *
     * @return string
     */
    public function getExpirationDate()
    {
        return trim($this->_rootElement->find($this->expirationDate)->getText());
    }

    /**
     * Get created by block.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return trim($this->_rootElement->find($this->created)->getText());
    }

    /**
     * Gets SKU list.
     *
     * @return array
     */
    public function getSkuList()
    {
        $skuArr = [];
        $this->waitForElementNotVisible($this->spinner);
        $elements = $this->_rootElement->getElements($this->sku);
        foreach ($elements as $element) {
            $skuArr[] = $element->getText();
        }
        return $skuArr;
    }

    /**
     * Gets QTY list.
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
     * Update quote products qty.
     *
     * @param array $qtys
     * @return void
     */
    public function updateQuoteProductsQty(array $qtys)
    {
        $this->waitForElementNotVisible($this->spinner);
        $elements = $this->_rootElement->getElements($this->qty);
        $i = 0;
        foreach ($elements as $element) {
            $element->setValue($qtys[$i]);
            $i++;
        }
        $this->waitForElementNotDisabled($this->updateButton);
        $this->_rootElement->find($this->updateButton)->click();
    }

    /**
     * Click send.
     *
     * @return void
     */
    public function send()
    {
        $this->_rootElement->find($this->sendButton)->click();
    }

    /**
     * Click delete.
     *
     * @return void
     */
    public function delete()
    {
        $this->_rootElement->find($this->deleteButton)->click();
        $this->waitForElementVisible($this->okButton);
        $this->browser->find($this->okButton)->click();
    }

    /**
     * Click close.
     *
     * @return void
     */
    public function close()
    {
        $this->_rootElement->find($this->closeButton)->click();
    }

    /**
     * Open comments tab.
     *
     * @return void
     */
    public function openCommentsTab()
    {
        $this->_rootElement->find($this->commentsTab)->click();
        $this->waitForElementVisible($this->comments);
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
     * Update comment.
     *
     * @param string $message
     * @return void
     */
    public function updateComment($message)
    {
        $this->waitForElementVisible($this->commentField);
        $this->_rootElement->find($this->commentField)->setValue($message);
    }

    /**
     * Click Go To Checkout.
     *
     * @return void
     */
    public function checkout()
    {
        $this->_rootElement->find($this->checkoutButton)->click();
    }

    /**
     * Click print link.
     *
     * @return void
     */
    public function clickPrint()
    {
        $this->waitForElementVisible($this->printLink);
        $this->_rootElement->find($this->printLink)->click();
    }

    /**
     * Get quote notification message.
     *
     * @return string
     */
    public function getNotificationMessage()
    {
        return $this->_rootElement->find($this->lock)->getText();
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
     * Check if checkout button is visible.
     *
     * @return bool
     */
    public function isCheckoutButtonVisible()
    {
        return $this->_rootElement->find($this->checkoutButton)->isVisible();
    }

    /**
     * Get proposed shipping price.
     *
     * @return string
     */
    public function getProposedShippingPrice()
    {
        return trim($this->_rootElement->find($this->proposedShippingPrice)->getText());
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
     * Get quote totals tax label.
     *
     * @return string
     */
    public function getQuoteTotalsSubtotalTaxLabel()
    {
        $this->_rootElement->find($this->quoteSubtotalToggleControl)->click();

        return trim($this->_rootElement->find($this->quoteTotalsTaxLabel)->getText());
    }

    /**
     * Wait for element is not disabled in the block.
     *
     * @param string $selector
     * @param string $strategy
     * @return bool|null
     */
    private function waitForElementNotDisabled($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $element = $browser->find($selector, $strategy);
                return $element->isDisabled() == false ? true : null;
            }
        );
    }
}
