<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddPaymentModalBox
 * @package DiamondNexus\Multipay\Block\Adminhtml\Order
 */
class AddPaymentModalBox extends AbstractPayment
{
        /**
         * @return float|string
         */
    public function getBalanceAmount()
    {
        $id = $this->getData('order')->getId();
        try {
            $transactions = $this->resource->getAllTransactionsByOrderId($id);
        } catch (LocalizedException $e) {
            return '';
        }
        $amount = $this->getData('order')->getGrandTotal();
        foreach ($transactions as $transaction) {
            $amount -= $transaction['amount'];
        }
        return $amount;
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        $orderId = false;
        if ($this->hasData('order')) {
            $orderId = $this->getData('order')->getId();
        }
        return $this->getUrl(
            'diamondnexus/order/order',
            [
            'order_id' => $orderId
            ]
        );
    }
}
