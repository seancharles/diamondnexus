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
 * Assert warning message appears
 */
class AssertReassignWarning extends AbstractConstraint
{

    /**
     * Assert warning message appears
     *
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function processAssert(
        SharedCatalogCompany $sharedCatalogCompany
    ) {
        $companyId = $sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);

        \PHPUnit\Framework\Assert::assertTrue(
            (bool)strpos(
                $sharedCatalogCompany->getModalBlock()->getText(),
                'This action will change a shared catalog for selected companies'
            ),
            'Message is wrong on company reassign.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Message is right on modal popup.';
    }
}
