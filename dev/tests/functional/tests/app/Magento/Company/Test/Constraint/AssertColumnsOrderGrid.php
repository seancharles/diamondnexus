<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert previous column title is correct.
 */
class AssertColumnsOrderGrid extends AbstractConstraint
{
    /**
     * Assert Previous column title is correct.
     *
     * @param OrderIndex $orderIndex
     * @param array $fieldData
     * @return void
     */
    public function processAssert(
        OrderIndex $orderIndex,
        array $fieldData
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            $orderIndex->getStructureGrid()->retrievePreviousColumnTitle($fieldData['title']),
            $fieldData['previous'],
            'Previous column title is not correct, expected ' . $fieldData['previous']
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Previous column title is correct.';
    }
}
