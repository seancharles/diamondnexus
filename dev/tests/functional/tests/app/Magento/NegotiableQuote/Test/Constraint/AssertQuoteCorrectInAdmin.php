<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;

/**
 * Class AssertQuoteCorrectInAdmin
 *
 * @SuppressWarnings(PHPMD)
 */
class AssertQuoteCorrectInAdmin extends AbstractConstraint
{
    /**
     * Check that quote contain correct data
     *
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param array $products
     * @param array $qtys
     * @param \DateTime $expirationDate
     * @param array $quote
     * @param array $messages
     * @param string $adminStatus
     * @param bool $adminLock
     * @param array $disabledButtonsAdmin
     * @param string $method
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param array $historyLog
     * @throws \Exception
     */
    public function processAssert(
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        array $products,
        array $qtys,
        \DateTime $expirationDate,
        array $quote,
        array $messages,
        $adminStatus,
        $adminLock,
        $disabledButtonsAdmin,
        $method,
        $tax,
        $discountType,
        $discountValue,
        $historyLog
    ) {
        $negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $negotiableQuoteGrid->getGrid()->searchAndOpen($filter);

        $this->checkProducts($products, $negotiableQuoteEdit);
        $this->checkQtys($qtys, $negotiableQuoteEdit);
        $this->checkExpirationDate($expirationDate, $negotiableQuoteEdit);
        $this->checkName($quote, $negotiableQuoteEdit);
        $this->checkStatus($adminStatus, $negotiableQuoteEdit);
        $this->checkLock($adminLock, $negotiableQuoteEdit);
        $this->checkDisabledButtons($disabledButtonsAdmin, $negotiableQuoteEdit);
        $this->checkShippingMethod($method, $negotiableQuoteEdit);
        $this->checkTotals($products, $qtys, $tax, $negotiableQuoteEdit, $discountType, $discountValue);
        $this->checkMessages($messages, $negotiableQuoteEdit);
        $this->checkHistoryLog($historyLog, $negotiableQuoteEdit);
    }

    /**
     * Check messages
     *
     * @param array $messages
     * @param NegotiableQuoteEdit $negotiableQuoteView
     */
    public function checkMessages(array $messages, NegotiableQuoteEdit $negotiableQuoteView)
    {
        $result = false;
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
            'Message is not correct.'
        );
    }

    /**
     * Check history log
     *
     * @param array $historyLog
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    public function checkHistoryLog(array $historyLog, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $result = false;
        $negotiableQuoteEdit->getQuoteDetails()->openHistoryLogTab();
        $log = $negotiableQuoteEdit->getQuoteDetails()->getHistoryLog();

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
            'History log is not correct'
        );
    }

    /**
     * Check totals
     *
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param NegotiableQuoteEdit $negotiableQuoteView
     */
    public function checkTotals(
        $products,
        $qtys,
        $tax,
        NegotiableQuoteEdit $negotiableQuoteView,
        $discountType,
        $discountValue
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
                ) === false) {
                    $result = false;
                }
                break;
            case 'proposed':
                if (strpos(str_replace(',', '', $totals['proposed_quote_price']), strval($discountValue)) === false) {
                    $result = false;
                }
                break;
            default:
                if (strpos(str_replace(',', '', $totals['proposed_quote_price']), strval($subtotal)) === false) {
                    $result = false;
                }
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
                if ($discountType == 'proposed' &&
                    strpos(
                        $totals['quote_tax'],
                        strval(floor(($discountValue) * $tax / 100))
                    ) === false) {
                    $result = false;
                }
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
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkShippingMethod($method, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteEdit->getQuoteDetails()->isMethodCorrect($method),
            'Method is not correct.'
        );
    }

    /**
     * Check quote products
     *
     * @param array $products
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkProducts(array $products, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $skuArr = [];
        foreach ($products as $product) {
            $skuArr[] = $product->getData('sku');
        }

        $result = array_diff($skuArr, $negotiableQuoteEdit->getQuoteDetails()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote products are not correct.'
        );
    }

    /**
     * Check quote qtys
     *
     * @param array $qtys
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkQtys(array $qtys, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        $result = array_diff($qtys, $negotiableQuoteEdit->getQuoteDetails()->getQtyList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote product qtys are not correct.'
        );
    }

    /**
     * Check quote expiration date
     *
     * @param \DateTime $expirationDate
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkExpirationDate(\DateTime $expirationDate, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $expirationDate->format('n/j/y'),
            $negotiableQuoteEdit->getQuoteDetails()->getExpirationDate(),
            'Quote expiration date is not correct.'
        );
    }

    /**
     * Check quote name
     *
     * @param array $quote
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkName(array $quote, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $negotiableQuoteEdit->getQuoteDetails()->getQuoteName(),
            'Quote name is not correct.'
        );
    }

    /**
     * Check quote status
     *
     * @param string $adminStatus
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkStatus($adminStatus, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $adminStatus,
            $negotiableQuoteEdit->getQuoteDetails()->getQuoteStatus(),
            'Quote status is not correct.'
        );
    }

    /**
     * Check quote is locked
     *
     * @param bool $adminLock
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkLock($adminLock, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $adminLock,
            $negotiableQuoteEdit->getQuoteDetails()->isLock(),
            'Quote lock is not correct.'
        );
    }

    /**
     * Check disabled buttons
     *
     * @param array $disabledButtons
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     */
    protected function checkDisabledButtons(array $disabledButtons, NegotiableQuoteEdit $negotiableQuoteEdit)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuoteEdit->getQuoteDetailsActions()->areButtonsDisabled($disabledButtons),
            'Disabled buttons are not correct.'
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
