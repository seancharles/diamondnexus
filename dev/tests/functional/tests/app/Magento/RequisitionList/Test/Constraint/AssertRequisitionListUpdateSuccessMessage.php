<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\RequisitionList\Test\Page\RequisitionListView;

/**
 * Assert that correct success message is displayed after requisition list update
 */
class AssertRequisitionListUpdateSuccessMessage extends AbstractConstraint
{
    /**
     * Assert that correct success message is displayed after requisition list update
     *
     * @param RequisitionListView $requisitionListView
     * @param array $products
     * @param string $productToUpdate
     */
    public function processAssert(
        RequisitionListView $requisitionListView,
        $products,
        $productToUpdate
    ) {
        $productName = '';
        foreach ($products as $product) {
            if (strpos($product->getSku(), $productToUpdate) !== false) {
                $productName = $product->getName();
                break;
            }
        }
        $message = $productName . ' has been updated in your requisition list.';

        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $requisitionListView->getMessagesBlock()->getSuccessMessage(),
            'Requisition list update success message is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list update success message is correct.';
    }
}
