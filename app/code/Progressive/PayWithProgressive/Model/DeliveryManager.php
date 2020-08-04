<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Model;

use Progressive\PayWithProgressive\Api\DeliveryManagerInterface;
use Progressive\PayWithProgressive\Helper\EcomSystem;

class DeliveryManager implements DeliveryManagerInterface
{
    private $_ecomSystem;

    public function __construct(
        EcomSystem $ecomSystem
    )
    {
        $this->_ecomSystem = $ecomSystem;
    }

    public function initDelivery()
    {
        // TODO: Implement initDelivery() method.
    }

    public function confirmDelivery($deliveryDate, $sessionId)
    {
        $payload = array();

        $payload['deliveryDate'] = $deliveryDate;
        $payload['sessionId'] = $sessionId;

        $this->_ecomSystem->postDeliveryConfirmation($payload);
    }

}