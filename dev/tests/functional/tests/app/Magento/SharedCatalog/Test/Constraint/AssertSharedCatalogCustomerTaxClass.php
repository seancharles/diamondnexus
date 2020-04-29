<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert shared catalog customer tax class.
 */
class AssertSharedCatalogCustomerTaxClass extends AbstractConstraint
{
    /**
     * Assert shared catalog customer tax class value.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @param SharedCatalog $sharedCatalog
     * @param string $customerTaxClass
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate,
        SharedCatalog $sharedCatalog,
        $customerTaxClass
    ) {
        $sharedCatalogIndex->open();
        $filter = ['name' => $sharedCatalog->getName()];
        $sharedCatalogIndex->getGrid()->search($filter);
        $sharedCatalogIndex->getGrid()->openEdit($sharedCatalogIndex->getGrid()->getFirstItemId());

        \PHPUnit\Framework\Assert::assertEquals(
            $customerTaxClass,
            $sharedCatalogCreate->getSharedCatalogForm()->getCustomerTaxClass(),
            'Shared catalog has wrong Customer Tax Class value.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog has right Customer Tax Class value.';
    }
}
