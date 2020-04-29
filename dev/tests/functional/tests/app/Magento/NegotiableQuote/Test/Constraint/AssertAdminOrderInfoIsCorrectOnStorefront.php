<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Class AssertAdminOrderInfoIsCorrectOnStorefront
 */
class AssertAdminOrderInfoIsCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param Customer $admin
     * @param string $adminOrderId
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        Customer $admin,
        $adminOrderId
    ) {
        $orderHistory->open();
        $orderHistory->getQuoteOrderShowBlock()->clickShowMyOrders();
        $orderHistory->getOrderHistoryBlock()->openOrderById($adminOrderId);
        $this->checkCreated($admin, $orderView);
    }

    /**
     * Check created values
     *
     * @param Customer $admin
     * @param CustomerOrderView $orderView
     */
    public function checkCreated(Customer $admin, CustomerOrderView $orderView)
    {
        $created = $orderView->getQuoteCreatedAtBlock()->getCreated();
        $result = strpos($created, $admin->getFirstname()) && strpos($created, $admin->getLastname());
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Created By name is not correct.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Company admin order info is correct';
    }
}
