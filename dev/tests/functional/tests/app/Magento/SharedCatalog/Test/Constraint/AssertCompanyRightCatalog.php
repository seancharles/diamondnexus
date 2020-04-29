<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert product with right shared catalog on grid
 */
class AssertCompanyRightCatalog extends AbstractConstraint
{
    /**
     * Assert product with right shared catalog on grid
     *
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    public function processAssert(
        SharedCatalogCompany $sharedCatalogCompany,
        SharedCatalog $sharedCatalog
    ) {
        $companyId = $sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $sharedCatalogName = $sharedCatalogCompany->getCompanyGrid()->getColumnValue($companyId, 'Catalog');
        \PHPUnit\Framework\Assert::assertEquals(
            $sharedCatalogName,
            $sharedCatalog->getName(),
            'Company is wrong on a shared catalog companies page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog was updated on company.';
    }
}
