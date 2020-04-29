<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Company\Test\Fixture\Company;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert confirmation message by order cancel.
 */
class AssertOrderCancelConfirmation extends AbstractConstraint
{
    /**
     * Confirmation popup title.
     */
    const CONFIRMATION_TITLE = 'Cancel the Order';

    /**
     * Confirmation message for company that is not active.
     */
    const CONFIRMATION_MESSAGE_PENDING = 'Are you sure you want to cancel this order? '
                        . 'The order amount will not be reverted to %s because the company is not active.';

    /**
     * Confirmation message for deleted company.
     */
    const CONFIRMATION_MESSAGE_DELETED = 'Are you sure you want to cancel this order? The order amount '
                    . 'will not be reverted to %s because the company associated with this customer does not exist.';

    /**
     * Assert confirmation message by order cancel.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param Company $company
     * @param string $confirmationMessage
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        Company $company,
        $confirmationMessage
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $salesOrderView->getOrderActions()->clickCancel();

        \PHPUnit\Framework\Assert::assertEquals(
            self::CONFIRMATION_TITLE,
            $salesOrderView->getOrderActions()->getConfirmationTitle(),
            'Confirmation title by order cancel is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            sprintf(constant('self::' . $confirmationMessage), $company->getCompanyName()),
            $salesOrderView->getOrderActions()->getConfirmationMessage(),
            'Confirmation message by order cancel is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Confirmation message by order cancel is correct.';
    }
}
