<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order;

use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order;

class AbstractPayment extends Template
{
    /**
     * @var Transaction
     */
    protected $resource;

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
        $this->resource = $transactionResource;
    }

    /**
     * @return bool
     */
    public function isMultipay()
    {
        /** @var Order $order */
        $order = $this->getData('order');
        $method = $order->getPayment()->getMethod();
        return $method == 'multipay' && $order->getTotalDue() != 0;
    }
}
