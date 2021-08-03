<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template\Context;
use Magento\CustomerBalance\Model\BalanceFactory;

class AddPaymentModalBox extends AbstractPayment
{
    protected $balanceFactory;

    /**
     * AddPaymentModalBox constructor.
     * @param Context $context
     * @param Transaction $transactionResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Transaction $transactionResource,
        BalanceFactory $balanceFactory,
        array $data = []
    ) {
        parent::__construct($context, $transactionResource, $data);
        
        $this->balanceFactory = $balanceFactory;
    }
    
        /**
         * @return float|string
         */
    public function getBalanceAmount()
    {
        return round($this->getData('order')->getTotalDue(), 2);
    }

    public function getStoreCreditAmount()
    {
        $customerId = $this->getData('order')->getCustomerId();
        $totalDue = round($this->getData('order')->getTotalDue(), 2);
        if ($customerId > 0) {
            $balanceModel = $this->balanceFactory->create();
            $balanceModel->setCustomerId($customerId)->loadByCustomer();

            if (round($totalDue, 2) < round($balanceModel->getAmount(), 2)) {
                return $totalDue;
            } else {
                return round($balanceModel->getAmount(), 2);
            }
        } else {
            return 0;
        }
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
