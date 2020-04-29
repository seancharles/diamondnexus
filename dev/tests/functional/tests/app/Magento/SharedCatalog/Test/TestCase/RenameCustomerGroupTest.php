<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Magento\Customer\Test\Fixture\CustomerGroup;

/**
 * Preconditions:
 * 1. Create shared catalog.
 *
 * Steps:
 * 1. Rename customer group.
 * 2. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68619
 */
class RenameCustomerGroupTest extends Injectable
{
    /**
     * @var \Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex
     */
    private $customerGroupIndex;

    /**
     * @var \Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew
     */
    private $customerGroupNew;

    /**
     * Inject pages.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroupNew $customerGroupNew
     * @return void
     */
    public function __inject(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroupNew $customerGroupNew
    ) {
        $this->customerGroupIndex = $customerGroupIndex;
        $this->customerGroupNew = $customerGroupNew;
    }

    /**
     * Rename customer group.
     *
     * @param SharedCatalog $sharedCatalog
     * @param CustomerGroup $customerGroup
     *
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, CustomerGroup $customerGroup)
    {
        $sharedCatalog->persist();
        $this->customerGroupIndex->open();
        $filter = ['code' => $sharedCatalog->getName()];
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $this->customerGroupNew->getPageMainForm()->fill($customerGroup);
        $this->customerGroupNew->getPageMainActions()->save();

        return [
            'sharedCatalogName' => $customerGroup->getCustomerGroupCode()
        ];
    }
}
