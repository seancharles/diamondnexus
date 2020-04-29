<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct success message appeared
 */
class AssertSuccessMessageAppeared extends AbstractConstraint
{
    /**
     * Assert that success message appeared on add to requisition list attempt is correct
     *
     * @param CustomerOrderView $orderView
     * @param string $name
     * @return void
     */
    public function processAssert(
        CustomerOrderView $orderView,
        $name
    ) {
        $successMessage =  $orderView->getRequisitionListMessages()->getSuccessMessage();
        $expectedMessage = sprintf('1 item(s) were added to the "%s"', $name);
        \PHPUnit\Framework\Assert::assertTrue(
            false !== strpos($successMessage, $expectedMessage),
            'Success message ' . $successMessage . ' is not equals to expected "'
            . $expectedMessage . ' " or is absent'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct success message is appeared on Storefront.';
    }
}
