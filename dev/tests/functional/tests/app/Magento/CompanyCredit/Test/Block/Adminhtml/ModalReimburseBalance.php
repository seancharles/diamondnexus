<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\Modal as ParentModal;
use Magento\Mtf\Client\Locator;

/**
 * Reimburse balance popup block.
 */
class ModalReimburseBalance extends ParentModal
{
    /**
     * Css locator for amount field.
     *
     * @var string
     */
    private $amount = '[name="reimburse_balance[amount]"]';

    /**
     * Css locator for purchase order number field.
     *
     * @var string
     */
    private $purchaseOrderNumber = '[name="reimburse_balance[purchase_order]"]';

    /**
     * Css locator for amount field.
     *
     * @var string
     */
    private $comment = '[name="reimburse_balance[credit_comment]"]';

    /**
     * Css locator for "Reimburse" button.
     *
     * @var string
     */
    private $saveButton = '.action-primary';

    /**
     * Css locator for spinner.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Xpath locator currency symbol.
     *
     * @var string
     */
    private $currencySymbol = '//div[@data-index="amount"]/div/div/label[@class="admin__addon-prefix"]/span';

    /**
     * Set reimburse balance amount.
     *
     * @param string $value
     * @return void
     */
    public function setAmount($value)
    {
        $this->_rootElement->find($this->amount)->setValue($value);
    }

    /**
     * Set reimburse balance purchase order number.
     *
     * @param string $value
     * @return void
     */
    public function setPurchaseOrderNumber($value)
    {
        $this->_rootElement->find($this->purchaseOrderNumber)->setValue($value);
    }

    /**
     * Set reimburse balance comment.
     *
     * @param string $value
     * @return void
     */
    public function setComment($value)
    {
        $this->_rootElement->find($this->comment)->setValue($value);
    }

    /**
     * Press Reimburse on a dialog.
     *
     * @return void
     */
    public function reimburse()
    {
        $this->_rootElement->find($this->saveButton)->click();
        $this->waitModalWindowToDisappear();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Get credit balance amount currency symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return trim($this->_rootElement->find($this->currencySymbol, Locator::SELECTOR_XPATH)->getText());
    }
}
