<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\InvoiceIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesInvoiceView;

/**
 * Check that invoice is correct.
 */
class AssertInvoiceCorrectInAdmin extends AbstractConstraint
{
    /**
     * Check that invoice is correct.
     *
     * @param InvoiceIndex $invoiceIndex
     * @param SalesInvoiceView $salesInvoiceView
     * @param array $ids
     * @param int $orderId
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param \Magento\GiftCardAccount\Test\Fixture\GiftCardAccount $giftCardAccount
     * @param \Magento\SalesRule\Test\Fixture\SalesRule $salesRule
     * @param string $discountType [optional]
     * @param int $discountValue [optional]
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        InvoiceIndex $invoiceIndex,
        SalesInvoiceView $salesInvoiceView,
        array $ids,
        $orderId,
        array $products,
        array $qtys,
        $tax,
        $giftCardAccount,
        $salesRule,
        $discountType = '',
        $discountValue = null
    ) {
        foreach ($ids['invoiceIds'] as $invoiceId) {
            $filter = [
                'order_id' => $orderId,
                'id' => $invoiceId,
            ];
            $invoiceIndex->open();
            $invoiceIndex->getInvoicesGrid()->searchAndOpen($filter);
            $this->checkOrderGrandTotal(
                $salesInvoiceView,
                $products,
                $qtys,
                $tax,
                $salesRule->getData(),
                $discountType,
                $discountValue
            );
            $this->verifyGiftCardDiscount($salesInvoiceView, $giftCardAccount->getData());
        }
    }

    /**
     * Check order grand total.
     *
     * @param SalesInvoiceView $salesInvoiceView
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param array $salesRule
     * @param string $discountType
     * @param int $discountValue
     */
    public function checkOrderGrandTotal(
        $salesInvoiceView,
        $products,
        $qtys,
        $tax,
        $salesRule,
        $discountType,
        $discountValue
    ) {
        $grandTotal = $salesInvoiceView->getTotalsBlock()->getOrderGrandTotal();
        $shippingAmount = (int)substr($salesInvoiceView->getTotalsBlock()->getShippingAmount(), 1);
        $result = true;
        $subtotal = 0;
        $i = 0;
        foreach ($products as $product) {
            $price = $product->getData('price') * $qtys[$i] * (100 - $salesRule['discount_amount']) / 100;
            $subtotal += $price;
            $i++;
        }
        switch ($discountType) {
            case 'amount':
                $subtotal = $subtotal - $discountValue;
                break;
            case 'percentage':
                $subtotal = $subtotal - ($subtotal * $discountValue / 100);
                break;
            case 'proposed':
                $subtotal = $discountValue;
                break;
        }
        $giftCardAmount = $salesInvoiceView->getTotalsBlock()->getGiftCardAmount() ?
            (int)substr($salesInvoiceView->getTotalsBlock()->getGiftCardAmount(), 2) : 0;
        preg_match('/\d+\.?\d+/', $grandTotal, $match);
        $uiPrice = array_shift($match);
        if (0.001 < ($uiPrice - ($subtotal + $shippingAmount + ($subtotal * $tax / 100) - $giftCardAmount))) {
            $result = false;
        }
        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Order grand total is not correct.'
        );
    }

    /**
     * Verify that gift card discount is correct.
     *
     * @param SalesInvoiceView $salesInvoiceView
     * @param array $giftCard
     */
    public function verifyGiftCardDiscount($salesInvoiceView, $giftCard)
    {
        $result = true;
        if ($salesInvoiceView->getTotalsBlock()->getGiftCardAmount() &&
            strpos($salesInvoiceView->getTotalsBlock()->getGiftCardAmount(), $giftCard['balance']) === false) {
            $result = false;
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Gift card discounts do not match.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice info is correct.';
    }
}
