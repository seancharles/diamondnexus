<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\RequisitionList\Test\Page\RequisitionListGrid;

/**
 * Assert that "Create New Requisition List" link is not visible in grid after RL limit is reached
 */
class AssertRequisitionListLinkIsNotVisible extends AbstractConstraint
{
    /**
     * Assert that "Create New Requisition List" link is not visible in grid after RL limit is reached
     *
     * @param CmsIndex $cmsIndex
     * @param RequisitionListGrid $requisitionListGrid
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        RequisitionListGrid $requisitionListGrid
    ) {
        $cmsIndex->getCmsPageBlock()->waitPageInit();
        \PHPUnit\Framework\Assert::assertFalse(
            $requisitionListGrid->getRequisitionListActions()->checkCreateLinkIsVisible(),
            'Requisition list link is visible.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list link is not visible';
    }
}
