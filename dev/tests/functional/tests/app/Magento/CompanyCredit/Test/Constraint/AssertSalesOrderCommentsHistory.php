<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that Comments History on "Order view" page.
 */
class AssertSalesOrderCommentsHistory extends AbstractConstraint
{
    /**
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $salesOrder
     * @param string $orderId
     * @param string $commentsHistory
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        $commentsHistory
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');

        \PHPUnit\Framework\Assert::assertEquals(
            $commentsHistory,
            $infoTab->getCommentsHistoryBlock()->getLatestComment()['comment'],
            'Comments history is not correct.'
        );
        /** @var \Magento\CompanyCredit\Test\Block\Adminhtml\Order\View\Tab\History $historyTab */
        $historyTab = $salesOrderView->getOrderForm()->openTab('history')->getTab('history');
        \PHPUnit\Framework\Assert::assertEquals(
            $commentsHistory,
            $historyTab->getCommentsHistoryBlock()->getLatestComment()['comment'],
            'Comments history is not correct on tab "Comments history".'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Comments history is correct.";
    }
}
