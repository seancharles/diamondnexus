<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\CustomerBalance\Model\BalanceFactory;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;

/**
 * Class AddPaymentModalBox
 * @package DiamondNexus\Multipay\Block\Adminhtml\Order
 */
class AddPaymentModalBox extends AbstractPayment
{
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
        return $amount = $this->getData('order')->getTotalDue();
    }
    
    public function getStoreCreditAmount()
    {
        $customerId = $this->getData('order')->getCustomerId();
        $totalDue = $this->getData('order')->getTotalDue();
        if($customerId > 0) {
            $balanceModel = $this->balanceFactory->create();
            $balanceModel->setCustomerId($customerId)->loadByCustomer();
            
            if($totalDue < $balanceModel->getAmount()) {
                return $totalDue;
            } else {
                return $balanceModel->getAmount();
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
