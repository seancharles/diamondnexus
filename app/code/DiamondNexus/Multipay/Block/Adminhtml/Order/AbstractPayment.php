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
        return $this->getData('order')->getPayment()->getMethod() == 'multipay';
    }
}
