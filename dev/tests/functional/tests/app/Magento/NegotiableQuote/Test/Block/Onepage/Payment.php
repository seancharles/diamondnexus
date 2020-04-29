<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Onepage checkout payment block.
 */
class Payment extends Block
{
    /**
     * Selector for active payment method.
     *
     * @var string
     */
    private $activePaymentMethodSelector = '.payment-method._active input';

    /**
     * Css selector edit address.
     *
     * @var string
     */
    private $editAddress = '.action.action-edit-address';

    /**
     * Css selector billing address.
     *
     * @var string
     */
    private $billingAddressSelect = '[name=billing_address_id]';

    /**
     * Address select New Address option label.
     *
     * @var string
     */
    private $newAddressOptionLabel = 'New Address';

    /**
     * Returns active payment method.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->_rootElement->find($this->activePaymentMethodSelector)->getValue();
    }

    /**
     * Select add new address option.
     *
     * @return void
     */
    public function selectAddNewAddressOption()
    {
        $this->_rootElement->find($this->editAddress)->click();
        $this->_rootElement->find($this->billingAddressSelect, Locator::SELECTOR_CSS, 'select')
            ->setValue($this->newAddressOptionLabel);
    }
}
