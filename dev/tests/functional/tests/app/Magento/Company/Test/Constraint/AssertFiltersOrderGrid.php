<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Filters are correct in Order Grid.
 */
class AssertFiltersOrderGrid extends AbstractConstraint
{
    /**
     * Assert Filters are correct in Order Grid.
     *
     * @param OrderIndex $orderIndex
     * @param array $companiesData
     * @param array $fieldData
     * @return void
     */
    public function processAssert(
        OrderIndex $orderIndex,
        array $companiesData,
        array $fieldData
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            $orderIndex->getStructureGrid()->retrievePreviousFilterTitle($fieldData['title']),
            $fieldData['previous'],
            'Previous filter title is not correct, expected ' . $fieldData['previous']
        );
        $companyData = array_shift($companiesData);
        $orderIndex->getStructureGrid()->search(['company_name' => $companyData['company']->getCompanyName()]);
        \PHPUnit\Framework\Assert::assertEquals(
            $orderIndex->getSalesOrderGrid()->getFirstItemId(),
            $companyData['order']->getId(),
            'Filter by Company Name is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Filters are correct.';
    }
}
