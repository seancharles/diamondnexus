<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\NegotiableQuote\Test\Page\NegotiableQuotePrintView;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Check that quote print contains correct data.
 *
 * @SuppressWarnings(PHPMD)
 */
class AssertQuotePrintCorrectOnStorefront extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteView $negotiableQuoteView
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     * @param BrowserInterface $browser
     * @param array $products
     * @param array $qtys
     * @param \DateTime $expirationDate
     * @param array $quote
     * @param array $messages
     * @param string $frontStatus
     * @param string $method
     * @param string $proposedShippingPrice
     * @param int $tax
     * @param string $discountType
     * @param int $discountValue
     * @param bool $frontDiscountApplied
     */
    public function processAssert(
        NegotiableQuoteView $negotiableQuoteView,
        NegotiableQuotePrintView $negotiableQuotePrintView,
        BrowserInterface $browser,
        array $products,
        array $qtys,
        \DateTime $expirationDate,
        array $quote,
        array $messages,
        $frontStatus,
        $method,
        $proposedShippingPrice,
        $tax,
        $discountType,
        $discountValue,
        $frontDiscountApplied
    ) {
        $negotiableQuoteView->getQuoteDetails()->clickPrint();
        $browser->selectWindow();
        $printWindow = $browser->getCurrentWindow();
        $negotiableQuotePrintView->getPrintQuoteDetails()->waitForBlock($quote);
        $this->checkProducts($products, $negotiableQuotePrintView);
        $this->checkQtys($qtys, $negotiableQuotePrintView);
        $this->checkExpirationDate($expirationDate, $negotiableQuotePrintView);
        $this->checkName($quote, $negotiableQuotePrintView);
        $this->checkStatus($frontStatus, $negotiableQuotePrintView);
        $this->checkShippingMethod($method, $negotiableQuotePrintView);
        $this->checkTotals(
            $products,
            $qtys,
            $tax,
            $proposedShippingPrice,
            $negotiableQuotePrintView,
            $discountType,
            $discountValue,
            $frontDiscountApplied
        );
        $this->checkMessages($messages, $negotiableQuotePrintView);

        $browser->closeWindow();
        if (in_array($printWindow, $browser->getWindowHandles())) {
            $browser->closeWindow($printWindow);
        }
    }

    /**
     * Check messages
     *
     * @param array $messages
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    public function checkMessages(array $messages, NegotiableQuotePrintView $negotiableQuotePrintView)
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
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     * @param string $discountType
     * @param int $discountValue
     * @param bool $frontDiscountApplied
     */
    public function checkTotals(
        $products,
        $qtys,
        $tax,
        $proposedShippingPrice,
        NegotiableQuotePrintView $negotiableQuotePrintView,
        $discountType,
        $discountValue,
        $frontDiscountApplied
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

        if ($frontDiscountApplied) {
            switch ($discountType) {
                case 'amount':
                    if (strpos(
                        str_replace(',', '', $totals['proposed_quote_price']),
                        strval($subtotal - $discountValue)
                    ) === false) {
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
                    if (strpos(
                        str_replace(',', '', $totals['proposed_quote_price']),
                        strval($discountValue)
                    ) === false) {
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
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkShippingMethod($method, NegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $negotiableQuotePrintView->getPrintQuoteDetails()->isMethodCorrect($method),
            'Method is not correct.'
        );
    }

    /**
     * @param string $frontStatus
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkStatus($frontStatus, NegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $frontStatus,
            $negotiableQuotePrintView->getPrintQuoteDetails()->getStatus(),
            'Quote status is not correct.'
        );
    }

    /**
     * @param array $quote
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkName(array $quote, NegotiableQuotePrintView $negotiableQuotePrintView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $quote['quote-name'],
            $negotiableQuotePrintView->getPrintQuoteDetails()->getQuoteName(),
            'Quote name is not correct.'
        );
    }

    /**
     * @param $expirationDate
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkExpirationDate(
        \DateTime $expirationDate,
        NegotiableQuotePrintView $negotiableQuotePrintView
    ) {
        \PHPUnit\Framework\Assert::assertEquals(
            $expirationDate->format('F j, Y'),
            $negotiableQuotePrintView->getPrintQuoteDetails()->getExpirationDate(),
            'Quote expiration date is not correct.'
        );
    }

    /**
     * @param array $qtys
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkQtys(array $qtys, NegotiableQuotePrintView $negotiableQuotePrintView)
    {
        $result = array_diff($qtys, $negotiableQuotePrintView->getPrintQuoteDetails()->getQtyList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Quote product qtys are not correct.'
        );
    }

    /**
     * @param array $products
     * @param NegotiableQuotePrintView $negotiableQuotePrintView
     */
    protected function checkProducts(array $products, NegotiableQuotePrintView $negotiableQuotePrintView)
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
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote data is correct.';
    }
}
