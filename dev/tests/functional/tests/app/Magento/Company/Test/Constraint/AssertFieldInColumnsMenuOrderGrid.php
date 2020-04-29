<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert fields are correct in "Columns" menu.
 */
class AssertFieldInColumnsMenuOrderGrid extends AbstractConstraint
{
    /**
     * Assert fields are correct in "Columns" menu.
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
            $orderIndex->getStructureGrid()->retrievePreviousTitleFieldInColumnsMenu($fieldData['title']),
            $fieldData['previous'],
            'Previous field is not correct, expected ' . $fieldData['previous']
        );

        $field = $orderIndex->getStructureGrid()->retrieveFieldInColumnsMenu($fieldData['title']);
        \PHPUnit\Framework\Assert::assertEquals(
            $field->find('[type="checkbox"]')->isSelected(),
            $fieldData['checked'],
            $fieldData['title'] . ' field is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Fields in "Columns" menu are correct.';
    }
}
