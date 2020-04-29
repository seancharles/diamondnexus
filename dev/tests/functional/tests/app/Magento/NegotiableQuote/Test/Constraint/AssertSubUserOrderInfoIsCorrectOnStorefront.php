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
 * Class AssertSubUserOrderInfoIsCorrectOnStorefront
 */
class AssertSubUserOrderInfoIsCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param Customer $subUser
     * @param string $subUserOrderId
     */
    public function processAssert(
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        Customer $subUser,
        $subUserOrderId
    ) {
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($subUserOrderId);
        $this->checkCreated($subUser, $orderView);
    }

    /**
     * Check created values
     *
     * @param Customer $subUser
     * @param CustomerOrderView $orderView
     */
    public function checkCreated(Customer $subUser, CustomerOrderView $orderView)
    {
        $created = $orderView->getQuoteCreatedAtBlock()->getCreated();
        $result = strpos($created, $subUser->getFirstname()) && strpos($created, $subUser->getLastname());
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
        return 'Company sub user order info is correct';
    }
}
