<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Progressive\PayWithProgressive\Helper\SessionHelper;
use Progressive\PayWithProgressive\Helper\EcomSystem;

class AfterOrderPlaceSuccess implements ObserverInterface
{

    /** @var \Progressive\PayWithProgressive\Helper\SessionHelper */
    protected $_session;
    protected $_ecom;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        SessionHelper $sessionHelper,
        EcomSystem $ecomSystem
    )
    {
        $this->_session = $sessionHelper;
        $this->_ecom = $ecomSystem;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Magento\Framework\Event\ObserverInterface::execute()
     *
     */
    public function execute(Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->_ecom->deleteClientToken();
    }
}

