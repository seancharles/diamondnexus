<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Quote configurable popup.
 */
class QuoteConfigurablePopup extends Block
{
    /**
     * Confirm button.
     *
     * @var string
     */
    protected $confirmButton = '.action-primary';

    /**
     * Xpath selector for bundle options select.
     *
     * @var string
     */
    protected $bundleOptionSelect = '//select[@id[starts-with(.,"bundle-option")]]';

    /**
     * Xpath selector for first option when configuring bundle product.
     *
     * @var string
     */
    protected $bundleOption = '//select/option[position()=2]';

    /**
     * Css selector for bundle options fieldset.
     *
     * @var string
     */
    protected $bundleOptionsFieldset = '#catalog_product_composite_configure_fields_bundle';

    /**
     * Selector for bundle product qty input.
     *
     * @var string
     */
    protected $qtyInput = '#product_composite_configure_input_qty';

    /**
     * Click Confirm button.
     *
     * @return $this
     */
    public function confirm()
    {
        $this->_rootElement->find($this->confirmButton)->click();

        return $this;
    }

    /**
     * Verify whether bundle select is visible.
     *
     * @return bool
     */
    public function isBundleSelectVisible()
    {
        $this->_rootElement->click();
        return $this->_rootElement->find($this->bundleOptionsFieldset)->isVisible();
    }

    /**
     * Select first option from bundle options select.
     *
     * @return void
     */
    public function selectOption()
    {
        $optionLabel = $this->_rootElement->find($this->bundleOption, Locator::SELECTOR_XPATH)->getText();
        $optionLabel = str_replace('   ', ' ' . html_entity_decode('&nbsp;') . ' ', $optionLabel);
        $this->_rootElement->find($this->bundleOptionSelect, Locator::SELECTOR_XPATH, 'select')->setValue($optionLabel);
    }

    /**
     * Update quote item qty.
     *
     * @param int $qty
     * @return void
     */
    public function updateQty($qty)
    {
        $this->_rootElement->find($this->qtyInput)->setValue($qty);
    }
}
