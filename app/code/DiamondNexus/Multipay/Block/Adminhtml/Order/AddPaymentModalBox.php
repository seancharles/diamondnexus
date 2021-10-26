<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template\Context;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Store\Model\StoreManagerInterface;

class AddPaymentModalBox extends AbstractPayment
{
    protected $balanceFactory;
    protected $storeManager;

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
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $transactionResource, $data);
        
        $this->balanceFactory = $balanceFactory;
        $this->storeManager = $storeManager;
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
            $storeId = $this->getData('order')->getStoreId();
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $balanceModel = $this->balanceFactory->create();
            $balanceModel->setWebsiteId($websiteId);
            $balanceModel->setCustomerId($customerId)->loadByCustomer();

            return $balanceModel->getAmount();

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
