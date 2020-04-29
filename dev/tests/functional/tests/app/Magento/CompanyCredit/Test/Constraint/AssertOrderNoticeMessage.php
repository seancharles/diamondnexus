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
 * Assert notice message on order page in Admin.
 */
class AssertOrderNoticeMessage extends AbstractConstraint
{
    /**
     * Notice message if a negative balance.
     */
    const NOTICE_MESSAGE_NEGATIVE_BALANCE = '%s has exceeded its credit limit $(%01.2f) by $%01.2f. '
                                        . 'Its outstanding balance, including this order, currently totals $%01.2f';

    /**
     * Notice message if a positive balance.
     */
    const NOTICE_MESSAGE_POSITIVE_BALANCE = 'The credit limit for %s is $%01.2f. Its outstanding balance, '
                                        . 'including this order, currently totals $%01.2f';

    /**
     * Assert notice message on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param Company $company
     * @param string $orderId
     * @param array $amounts
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        Company $company,
        $orderId,
        array $amounts
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $companyName = $company->getCompanyName();
        $noticeMessage = $amounts['creditLimit'] < $amounts['orderTotal'] ?
            sprintf(
                self::NOTICE_MESSAGE_NEGATIVE_BALANCE,
                $companyName,
                $amounts['creditLimit'],
                $amounts['orderTotal'],
                $amounts['outstandingBalance']
            ) :
            str_replace('$-', '-$', sprintf(
                self::NOTICE_MESSAGE_POSITIVE_BALANCE,
                $companyName,
                $amounts['creditLimit'],
                $amounts['outstandingBalance']
            ));

        \PHPUnit\Framework\Assert::assertEquals(
            $noticeMessage,
            $salesOrderView->getMessagesBlock()->getNoticeMessage(),
            'Notice message on order page in Admin is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Notice message on order page in Admin Panel is correct.';
    }
}
