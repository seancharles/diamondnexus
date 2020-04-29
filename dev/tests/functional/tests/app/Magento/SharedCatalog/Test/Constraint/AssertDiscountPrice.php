<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert discount pricing is calculated right.
 */
class AssertDiscountPrice extends AbstractConstraint
{
    /**
     * Assert discount pricing is calculated right.
     *
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param array $data
     * @return void
     */
    public function processAssert(
        SharedCatalogConfigure $sharedCatalogConfigure,
        array $data = []
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            number_format($data['discount']),
            $sharedCatalogConfigure->getPricingGrid()->retrieveInputValue(),
            'Discount pricing is calculated wrong.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Discount pricing was calculated.';
    }
}
