<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\RequisitionList\Test\Page\RequisitionListGrid;
use Magento\RequisitionList\Test\Page\RequisitionListView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

/**
 * Assert that edit product link is not visible for GiftCard products
 */
class AssertEditLinkIsNotVisibleInRequisitionList extends AbstractConstraint
{
    /**
     * Assert that edit product link is not visible for GiftCard products
     *
     * @param RequisitionListGrid $requisitionListGrid
     * @param RequisitionListView $requisitionListView
     * @return void
     */
    public function processAssert(
        RequisitionListGrid $requisitionListGrid,
        RequisitionListView $requisitionListView
    ) {
        $requisitionListGrid->open();
        $requisitionListGrid->getRequisitionListGrid()->openFirstItem();

        \PHPUnit\Framework\Assert::assertFalse(
            $requisitionListView->getRequisitionListContent()->isEditLinkVisible(),
            'Edit link is visible.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Edit link is not visible in requisition list.';
    }
}
