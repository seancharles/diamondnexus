<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Block\Adminhtml\Logs\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Payment;
use Mageplaza\EditOrder\Block\Adminhtml\Logs\View;

/**
 * Class PaymentMethod
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs\Order
 */
class PaymentMethod extends View
{
    /**
     * @return bool
     */
    public function checkPaymentUpdated()
    {
        $oldData = $this->getOldOrderData();
        $newData = $this->getNewOrderData();

        if (isset($newData['payment'], $oldData['payment'])) {
            foreach ($newData['payment'] as $key => $val) {
                if ($newData['payment'][$key] !== $oldData['payment'][$key]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getOldPaymentData()
    {
        $oldData = $this->getOldOrderData();

        return $oldData['payment'];
    }

    /**
     * @return Payment
     */
    public function getOldPayment()
    {
        $quote = $this->quoteFactory->create()->load($this->getOrder()->getQuoteId());

        return $quote->getPayment();
    }

    /**
     * @param Payment $payment
     *
     * @return string
     */
    public function getPaymentHtml($payment)
    {
        try {
            $html = $this->_paymentData->getInfoBlock($payment, $this->getLayout())->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }
}
