<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Customer;

use Magento\Mtf\Block\Block;

/**
 * Class EditAddresses
 */
class EditAddresses extends Block
{
    /**
     * Delete address css selector
     *
     * @var string
     */
    protected $deleteAddressLinkSelector = '.action-delete';

    /**
     * Customer addresses list grid.
     *
     * @var string
     */
    private $customerAddressesGrid = '.customer_form_areas_address_address_customer_address_listing';

    /**
     * Delete default address
     */
    public function deleteDefaultAddress()
    {
        $addressesGrid = $this->getCustomerAddressesGrid();
        $firstAddressRow = $addressesGrid->getFirstRow();
        $addressesGrid->deleteRowItemAddress($firstAddressRow);
    }

    /**
     * Get New Category Modal Form.
     *
     * @return \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressesGrid
     */
    private function getCustomerAddressesGrid()
    {
        return $this->blockFactory->create(
            \Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses\AddressesGrid::class,
            ['element' => $this->browser->find($this->customerAddressesGrid)]
        );
    }
}
