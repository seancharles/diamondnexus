<?php

namespace DiamondNexus\Multipay\Model\Payment;

class Multipay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "multipay";
    protected $_isOffline = true;
}
