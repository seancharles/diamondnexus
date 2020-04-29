<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Abstract Test delete entity on Storefront.
 */
abstract class AbstractDeleteEntityStorefrontTest extends AbstractCreateEntityStorefrontTest
{
    /**
     * Delete entity from Storefront.
     *
     * @param Customer $customer
     * @param string $entity
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, $entity, $configData = null)
    {
        $return = parent::test($customer, $entity, $configData);

        $this->companyPage->open();
        $this->companyPage->getTree()->selectFirstChild();
        $this->companyPage->getTreeControl()->clickDeleteSelected();
        $this->companyPage->getDeletePopup()->submit();

        return $return;
    }
}
