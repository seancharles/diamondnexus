<?php

namespace DiamondNexus\Multipay\Model;

class Constant
{
    const MULTIPAY_METHOD = 'multipay';

    const MULTIPAY_CREDIT_METHOD = 1;
    const MULTIPAY_CASH_METHOD = 2;
    const MULTIPAY_QUOTE_METHOD = 3;
    const MULTIPAY_STORE_CREDIT_METHOD = 4;
    const MULTIPAY_AFFIRM_OFFLINE_METHOD = 5;
    const MULTIPAY_PAYPAL_OFFLINE_METHOD = 6;
    const MULTIPAY_PROGRESSIVE_OFFLINE_METHOD = 7;

    const MULTIPAY_METHOD_LABEL = array(
        1 => 'Credit',
        2 => 'Cash',
        3 => 'Quote',
        4 => 'Store Credit',
        5 => 'Affirm (offline)',
        6 => 'Paypal (offline)',
        7 => 'Progressive Leasing (offline)',
    );

    const MULTIPAY_SALE_ACTION = 1;
    const MULTIPAY_REFUND_ACTION = 2;
    const MULTIPAY_CANCEL_ACTION = 3;

    const STATE_QUOTE = 'quote';
}