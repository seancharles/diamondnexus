<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\RequisitionList\Test\Page\RequisitionListView;

/**
 * Assert that correct product information is displayed in requisition list after item update.
 */
class AssertProductIsCorrectAfterUpdate extends AbstractConstraint
{
    /**
     * Assert that correct product information is displayed in requisition list after item update.
     *
     * @param RequisitionListView $requisitionListView
     * @param string $productToUpdate
     * @param array $updateData
     * @return void
     */
    public function processAssert(
        RequisitionListView $requisitionListView,
        $productToUpdate,
        $updateData
    ) {
        $qty = $requisitionListView->getRequisitionListContent()->getQty($productToUpdate);
        $options = $requisitionListView->getRequisitionListContent()->getOptionValues($productToUpdate);
        $this->checkQty($updateData, $qty);
        $this->checkOptions($updateData, $options);
    }

    /**
     * Check is requisition list item qty correct.
     *
     * @param array $updateData
     * @param int $qty
     * @return void
     */
    public function checkQty($updateData, $qty)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $qty,
            $updateData['qty'],
            'Updated qty is incorrect.'
        );
    }

    /**
     * Check are requisition list item options correct.
     *
     * @param array $updateData
     * @param array $options
     * @return void
     */
    public function checkOptions($updateData, $options)
    {
        $result = false;
        foreach ($updateData['options'] as $option) {
            foreach ($options as $opt) {
                if (strpos($opt, $option['label']) !== false) {
                    $result = true;
                    break;
                }
            }
            if (!$result) {
                break;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Updated options are incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Item info is correct.';
    }
}
