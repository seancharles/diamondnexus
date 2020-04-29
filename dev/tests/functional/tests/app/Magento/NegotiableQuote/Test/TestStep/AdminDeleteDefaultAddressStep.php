<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Admin delete default address in admin panel.
 */
class AdminDeleteDefaultAddressStep implements TestStepInterface
{
    /**
     * @var CustomerIndexEdit
     */
    private $customerIndexEdit;

    /**
     * @param CustomerIndexEdit $customerIndexEdit
     */
    public function __construct(
        CustomerIndexEdit $customerIndexEdit
    ) {
        $this->customerIndexEdit = $customerIndexEdit;
    }

    /**
     * Admin delete default address in admin panel.
     *
     * @return array
     */
    public function run()
    {
        $this->customerIndexEdit->getAddressesBlock()->openAddressesBlock();
        $this->customerIndexEdit->getEditAddressesBlock()->deleteDefaultAddress();

        return [];
    }
}
