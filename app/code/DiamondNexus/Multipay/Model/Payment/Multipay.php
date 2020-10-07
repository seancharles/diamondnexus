<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model\Payment;

/**
 * Class Multipay
 * @package DiamondNexus\Multipay\Model\Payment
 */
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

