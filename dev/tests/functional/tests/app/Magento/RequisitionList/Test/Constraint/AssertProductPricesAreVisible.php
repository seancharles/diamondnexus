<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\RequisitionList\Test\Page\RequisitionListView;
use Magento\RequisitionList\Test\Page\RequisitionListGrid;

/**
 * Assert that both prices including and excluding tax are visible
 */
class AssertProductPricesAreVisible extends AbstractConstraint
{
    /**
     * Assert that both prices including and excluding tax are visible
     *
     * @param RequisitionListView $requisitionListView
     * @param RequisitionListGrid $requisitionListGrid
     */
    public function processAssert(
        RequisitionListView $requisitionListView,
        RequisitionListGrid $requisitionListGrid
    ) {
        $requisitionListGrid->open();
        $requisitionListGrid->getRequisitionListGrid()->openFirstItem();

        \PHPUnit\Framework\Assert::assertTrue(
            $requisitionListView->getRequisitionListContent()->arePricesVisible(),
            'Prices are not visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Both including and excluding tax prices are visible.';
    }
}
