<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\Adminhtml\AdminNegotiableQuotePrintView;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Class AssertQuotePrintCorrectInAdmin
 *
 * @SuppressWarnings(PHPMD)
 */
class AssertQuotePrintCorrectInAdmin extends AbstractConstraint
{
    /**
     * Check that quote contain correct data
     *
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     * @param NegotiableQuoteEdit $negotiableQuoteEdit
     * @param BrowserInterface $browser
     * @param array $products
     * @param array $qtys
     * @param \DateTime $expirationDate
     * @param array $quote
     * @param array $messages
     * @param string $adminStatus
     * @param string $method
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @throws \Exception
     */
    public function processAssert(
        AdminNegotiableQuotePrintView $negotiableQuotePrintView,
        NegotiableQuoteEdit $negotiableQuoteEdit,
        BrowserInterface $browser,
        array $products,
        array $qtys,
        \DateTime $expirationDate,
        array $quote,
        array $messages,
        $adminStatus,
        $method,
        $tax,
        $discountType,
        $discountValue
    ) {
        $negotiableQuoteEdit->getQuoteDetailsActions()->clickPrint();
        $browser->selectWindow();
        $this->checkProducts($products, $negotiableQuotePrintView);
        $this->checkQtys($qtys, $negotiableQuotePrintView);
        $this->checkExpirationDate($expirationDate, $negotiableQuotePrintView);
        $this->checkName($quote, $negotiableQuotePrintView);
        $this->checkStatus($adminStatus, $negotiableQuotePrintView);
        $this->checkShippingMethod($method, $negotiableQuotePrintView);
        $this->checkTotals($products, $qtys, $tax, $negotiableQuotePrintView, $discountType, $discountValue);
        $this->checkMessages($messages, $negotiableQuotePrintView);

        $browser->closeWindow();
    }

    /**
     * Check messages
     *
     * @param array $messages
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    public function checkMessages(array $messages, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        $result = false;
        $dialog = $negotiableQuotePrintView->getPrintQuoteDetails()->getComments();

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
     * Check totals
     *
     * @param array $products
     * @param array $qtys
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    public function checkTotals(
        $products,
        $qtys,
        $tax,
        AdminNegotiableQuotePrintView $negotiableQuotePrintView,
        $discountType,
        $discountValue
    ) {
        $totals = $negotiableQuotePrintView->getPrintQuoteDetails()->getTotals();

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
                ) === false
                ) {
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
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkShippingMethod($method, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuotePrintView->getPrintQuoteDetails()->isMethodCorrect($method),
            'Method is not correct.'
        );
    }

    /**
     * Check quote products
     *
     * @param array $products
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkProducts(array $products, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        $skuArr = [];
        foreach ($products as $product) {
            $skuArr[] = $product->getData('sku');
        }

        $result = array_diff($skuArr, $negotiableQuotePrintView->getPrintQuoteDetails()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote products are not correct.'
        );
    }

    /**
     * Check quote qtys
     *
     * @param array $qtys
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkQtys(array $qtys, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        $result = array_diff($qtys, $negotiableQuotePrintView->getPrintQuoteDetails()->getQtyList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote product qtys are not correct.'
        );
    }

    /**
     * Check quote expiration date
     *
     * @param \DateTime $expirationDate
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkExpirationDate(
        \DateTime $expirationDate,
        AdminNegotiableQuotePrintView $negotiableQuotePrintView
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            $expirationDate->format('M j, Y'),
            $negotiableQuotePrintView->getPrintQuoteDetails()->getExpirationDate(),
            'Quote expiration date is not correct.'
        );
    }

    /**
     * Check quote name
     *
     * @param array $quote
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkName(array $quote, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $negotiableQuotePrintView->getPrintQuoteDetails()->getQuoteName(),
            'Quote name is not correct.'
        );
    }

    /**
     * Check quote status
     *
     * @param string $adminStatus
     * @param AdminNegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkStatus($adminStatus, AdminNegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $adminStatus,
            $negotiableQuotePrintView->getPrintQuoteDetails()->getQuoteStatus(),
            'Quote status is not correct.'
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
