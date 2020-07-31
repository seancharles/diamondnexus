<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class PaymentMethodAvailable implements ObserverInterface
{

    protected $_logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($observer->getEvent()->getMethodInstance()->getCode() == "progressive_gateway")
        {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', true);
        }
    }

}