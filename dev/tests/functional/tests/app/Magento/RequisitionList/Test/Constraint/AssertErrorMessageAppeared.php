<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct error message is appeared
 */
class AssertErrorMessageAppeared extends AbstractConstraint
{
    /**
     * Order view page.
     *
     * @var CustomerOrderView
     */
    protected $orderView;

    /**
     * Assert that error message appeared on the add to requisition list attempt is correct
     *
     * @param CustomerOrderView $orderView
     * @param string $expectedMessage
     * @return void
     */
    public function processAssert(
        CustomerOrderView $orderView,
        $expectedMessage
    ) {
        $errorMessage =  $orderView->getRequisitionListMessages()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertTrue(
            false !== strpos($errorMessage, $expectedMessage),
            'Error message ' . $errorMessage . ' is not equals to expected "'
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
        return 'Error message is appeared on Storefront.';
    }
}
