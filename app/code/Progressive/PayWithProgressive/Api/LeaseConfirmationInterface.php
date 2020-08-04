<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Api;


interface LeaseConfirmationInterface
{
    /**
     * @return mixed
     */
    public function confirmLease();
}