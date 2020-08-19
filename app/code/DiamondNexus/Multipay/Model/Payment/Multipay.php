<?php
namespace DiamondNexus\Multipay\Model\Payment;

class Multipay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "multipay";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}