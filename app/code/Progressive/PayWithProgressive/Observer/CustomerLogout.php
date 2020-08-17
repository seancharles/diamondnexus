<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Progressive\PayWithProgressive\Helper\SessionHelper;

class CustomerLogout implements ObserverInterface
{
    /** @var \Magento\Framework\Logger\Monolog */
    protected $_session;

    /**
     *
     * @param $sessionHelper
     */
    public function __construct(
        SessionHelper $sessionHelper
    )
    {
        $this->_session = $sessionHelper;
    }

    /**
     *
     * @see \Magento\Framework\Event\ObserverInterface::execute()
     * @param $observer
     */
    public function execute(Observer $observer)
    {
        $this->_session->updateLogout();
    }
}

