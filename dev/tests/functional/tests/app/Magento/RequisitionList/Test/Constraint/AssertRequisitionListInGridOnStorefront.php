<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\RequisitionList\Test\Page\RequisitionListGrid;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that requisition list is present in grid on Storefront
 */
class AssertRequisitionListInGridOnStorefront extends AbstractConstraint
{
    /**
     * Assert that requisition list is present in grid on Storefront
     *
     * @param RequisitionListGrid $requisitionListGrid
     * @param string $name
     * @return void
     */
    public function processAssert(
        RequisitionListGrid $requisitionListGrid,
        $name
    ) {
        $filter = [
            'name' => $name,
        ];
        $requisitionListGrid->open();
        \PHPUnit\Framework\Assert::assertTrue(
            $requisitionListGrid->getRequisitionListGrid()->isRequisitionListVisible($filter),
            'Requisition list with following name \'' . $name . '\' is absent in grid on Storefront.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list is present in grid on Storefront.';
    }
}
