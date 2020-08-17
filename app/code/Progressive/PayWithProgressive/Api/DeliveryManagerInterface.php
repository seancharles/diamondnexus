<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Api;


interface DeliveryManagerInterface
{
    /**
     * Initialize Delivery Manager
     * @return mixed
     */
    public function initDelivery();

    /**
     * confirmation
     *
     * @param string $deliveryDate
     * @param string $sessionId
     *
     * @return mixed
     */
    public function confirmDelivery($deliveryDate, $sessionId);



}