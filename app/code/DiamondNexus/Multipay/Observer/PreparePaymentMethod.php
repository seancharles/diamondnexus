<?php

namespace DiamondNexus\Multipay\Observer;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreparePaymentMethod implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $paymentCode = $observer->getMethodInstance()->getCode();
        $result = $observer->getResult();
        $result->setData('is_available', $paymentCode === Constant::MULTIPAY_METHOD);
        return $this;
    }
}
