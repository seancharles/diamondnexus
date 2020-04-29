<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;

/**
 * Check that quote contains right data.
 *
 * @SuppressWarnings(PHPMD)
 */
class AssertQuoteCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param array $products
     * @param array $qtys
     * @param \DateTime $expirationDate
     * @param array $quote
     * @param array $messages
     * @param string $frontStatus
     * @param bool $frontLock
     * @param array $disabledButtonsFront
     * @param string $method
     * @param string $proposedShippingPrice
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param array $historyLog
     * @param bool $frontDiscountApplied
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        NegotiableQuoteView $negotiableQuoteView,
        array $products,
        array $qtys,
        \DateTime $expirationDate,
        array $quote,
        array $messages,
        $frontStatus,
        $frontLock,
        array $disabledButtonsFront,
        $method,
        $proposedShippingPrice,
        $tax,
        $discountType,
        $discountValue,
        $historyLog,
        $frontDiscountApplied
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();

        $this->checkProducts($products, $negotiableQuoteView);
        $this->checkQtys($qtys, $negotiableQuoteView);
        $this->checkExpirationDate($expirationDate, $negotiableQuoteView);
        $this->checkName($quote, $negotiableQuoteView);
        $this->checkStatus($frontStatus, $negotiableQuoteView);
        $this->checkLock($frontLock, $negotiableQuoteView);
        $this->checkDisabledButtons($disabledButtonsFront, $negotiableQuoteView);
        $this->checkShippingMethod($method, $negotiableQuoteView);
        $this->checkTotals(
            $products,
            $qtys,
            $tax,
            $proposedShippingPrice,
            $negotiableQuoteView,
            $discountType,
            $discountValue,
            $frontDiscountApplied
        );
        $this->checkMessages($messages, $negotiableQuoteView);
        $this->checkHistoryLog($historyLog, $negotiableQuoteView);
    }

    /**
     * Check messages
     *
     * @param array $messages
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function checkMessages(array $messages, NegotiableQuoteView $negotiableQuoteView)
    {
        $result = false;
        $negotiableQuoteView->getQuoteDetails()->openCommentsTab();
        $dialog = $negotiableQuoteView->getQuoteDetails()->getComments();

        foreach ($messages as $message) {
            foreach ($dialog as $dialogMessage) {
                if (strpos($dialogMessage, $message) !== false) {
                    $result = true;
                    break;
                }
            }
            if (!$result) {
                break;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Customer message is not correct'
        );
    }

    /**
     * Check totals
     *
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param float $proposedShippingPrice
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param string $discountType
     * @param int $discountValue
     * @param bool $frontDiscountApplied
     */
    public function checkTotals(
        $products,
        $qtys,
        $tax,
        $proposedShippingPrice,
        NegotiableQuoteView $negotiableQuoteView,
        $discountType,
        $discountValue,
        $frontDiscountApplied
    ) {
        $totals = $negotiableQuoteView->getQuoteDetails()->getTotals();
        $result = true;
        $subtotal = 0;
        $i = 0;

        foreach ($products as $product) {
            $productPrice = $product->getData('tier_price') ?
                $product->getData('tier_price')[1]['price'] : $product->getData('price');
            $price = $productPrice * $qtys[$i];
            $subtotal += $price;
            $i++;
        }

        if ($frontDiscountApplied) {
            switch ($discountType) {
                case 'amount':
                    if (strpos(
                        str_replace(',', '', $totals['proposed_quote_price']),
                        strval($subtotal - $discountValue)
                    ) === false
                    ) {
                        $result = false;
                    }
                    break;
                case 'percentage':
                    if (strpos(
                        str_replace(',', '', $totals['proposed_quote_price']),
                        strval($subtotal - ($subtotal * $discountValue / 100))
                    ) === false
                    ) {
                        $result = false;
                    }
                    break;
                case 'proposed':
                    if (strpos(
                        str_replace(',', '', $totals['proposed_quote_price']),
                        strval($discountValue)
                    ) === false
                    ) {
                        $result = false;
                    }
                    break;
                default:
                    if (strpos(str_replace(',', '', $totals['proposed_quote_price']), strval($subtotal)) === false) {
                        $result = false;
                    }
            }
        }

        if ($proposedShippingPrice && strpos($totals['proposed_shipping'], strval($proposedShippingPrice)) === false) {
            $result = false;
        }

        if ($tax) {
            if ($discountType == 'percentage' &&
                strpos(
                    $totals['quote_tax'],
                    strval(floor(($subtotal - ($subtotal * $discountValue) / 100) * $tax / 100))
                ) === false) {
                $result = false;
            }
            if ($discountType == 'amount' &&
                strpos(
                    $totals['quote_tax'],
                    strval(floor(($subtotal - $discountValue) * $tax / 100))
                ) === false) {
                $result = false;
            }
            if ($discountType == 'proposed' &&
                strpos(
                    $totals['quote_tax'],
                    strval(floor(($discountValue) * $tax / 100))
                ) === false) {
                $result = false;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Totals are not correct'
        );
    }

    /**
     * Check shippingMethod
     *
     * @param string $method
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkShippingMethod($method, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteView->getQuoteDetails()->isMethodCorrect($method),
            'Method is not correct.'
        );
    }

    /**
     * Check disabled buttons
     *
     * @param array $disabledButtons
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkDisabledButtons(array $disabledButtons, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteView->getQuoteDetails()->areButtonsDisabled($disabledButtons),
            'Disabled buttons are not correct.'
        );
    }

    /**
     * @param bool $frontLock
     * @param $negotiableQuoteView
     */
    protected function checkLock($frontLock, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $frontLock,
            $negotiableQuoteView->getQuoteDetails()->isLock(),
            'Quote lock is not correct.'
        );
    }

    /**
     * @param string $frontStatus
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkStatus($frontStatus, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $frontStatus,
            $negotiableQuoteView->getQuoteDetails()->getStatus(),
            'Quote status is not correct.'
        );
    }

    /**
     * @param array $quote
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    protected function checkName(array $quote, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $negotiableQuoteView->getQuoteDetails()->getQuoteName(),
            'Quote name is not correct.'
        );
    }

    /**
     * @param $expirationDate
     * @param $negotiableQuoteView
     */
    protected function checkExpirationDate(\DateTime $expirationDate, NegotiableQuoteView $negotiableQuoteView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $expirationDate->format('F j, Y'),
            $negotiableQuoteView->getQuoteDetails()->getExpirationDate(),
            'Quote expiration date is not correct.'
        );
    }

    /**
     * @param array $qtys
     * @param $negotiableQuoteView
     */
    protected function checkQtys(array $qtys, NegotiableQuoteView $negotiableQuoteView)
    {
        $result = array_diff($qtys, $negotiableQuoteView->getQuoteDetails()->getQtyList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote product qtys are not correct.'
        );
    }

    /**
     * @param array $products
     * @param $negotiableQuoteView
     */
    protected function checkProducts(array $products, NegotiableQuoteView $negotiableQuoteView)
    {
        $skuArr = [];
        foreach ($products as $product) {
            $skuArr[] = $product->getData('sku');
        }

        $result = array_diff($skuArr, $negotiableQuoteView->getQuoteDetails()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote products are not correct.'
        );
    }

    /**
     * Check history log
     *
     * @param array $historyLog
     * @param NegotiableQuoteView $negotiableQuoteView
     */
    public function checkHistoryLog(array $historyLog, NegotiableQuoteView $negotiableQuoteView)
    {
        $result = false;
        $negotiableQuoteView->getQuoteDetails()->openHistoryLogTab();
        $log = $negotiableQuoteView->getQuoteDetails()->getHistoryLog();

        foreach ($historyLog as $message) {
            foreach ($log as $logMessage) {
                if (strpos($logMessage, $message) !== false) {
                    $result = true;
                    break;
                }
            }
            if (!$result) {
                break;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Customer log is not correct'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote data is correct.';
    }
}
