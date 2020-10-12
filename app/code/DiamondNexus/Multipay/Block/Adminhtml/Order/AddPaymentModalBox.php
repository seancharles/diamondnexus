<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddPaymentModalBox
 * @package DiamondNexus\Multipay\Block\Adminhtml\Order
 */
class AddPaymentModalBox extends Template
{
    /**
     * @var Transaction
     */
    protected $transactionResource;

    /**
     * AddPaymentModalBox constructor.
     * @param Context $context
     * @param Transaction $transactionResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Transaction $transactionResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->transactionResource = $transactionResource;
    }

    /**
     * @return bool
     */
    public function isMultipay()
    {
        return $this->getData('order')->getPayment()->getMethod() == 'multipay';
    }

    /**
     * @return float|string
     */
    public function getBalanceAmount()
    {
        $orderId = $this->getData('order')->getId();
        try {
            $allTransactions = $this->transactionResource->getAllTransactionsByOrderId($orderId);
        } catch (LocalizedException $e) {
            return '';
        }
        $amount = $this->getData('order')->getGrandTotal();
        foreach ($allTransactions as $transaction) {
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
        return $this->getUrl('diamondnexus/order/order', [
            'order_id' => $orderId
        ]);
    }
}
