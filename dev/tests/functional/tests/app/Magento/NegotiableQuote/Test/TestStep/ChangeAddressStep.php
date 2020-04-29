<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Customer\Test\Fixture\Address;

/**
 * Change address in negotiable quote.
 */
class ChangeAddressStep implements TestStepInterface
{
    /**
     * @var CustomerAddressEdit
     */
    private $customerAddressEdit;

    /**
     * @var NegotiableQuoteGrid
     */
    private $quoteFrontendGrid;

    /**
     * @var NegotiableQuoteView
     */
    private $quoteFrontendView;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var array
     */
    private $updateData;

    /**
     * @param Address $address
     * @param CustomerAddressEdit $customerAddressEdit
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendView
     * @param array $updateData
     */
    public function __construct(
        Address $address,
        CustomerAddressEdit $customerAddressEdit,
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendView,
        array $updateData
    ) {
        $this->address = $address;
        $this->customerAddressEdit = $customerAddressEdit;
        $this->quoteFrontendGrid = $quoteFrontendGrid;
        $this->quoteFrontendView = $quoteFrontendView;
        $this->updateData = $updateData;
    }

    /**
     * Change address in negotiable quote.
     *
     * @return array
     */
    public function run()
    {
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->clickEditAddress();
        $this->customerAddressEdit->getEditForm()->editCustomerAddress($this->address);

        return ['address' => $this->address];
    }
}
