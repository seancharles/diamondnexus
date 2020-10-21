<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model;

/**
 * Class Constant
 * @package DiamondNexus\Multipay\Model
 */
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

    const MULTIPAY_METHOD_LABEL = [
        1 => 'Credit',
        2 => 'Cash',
        3 => 'Quote',
        4 => 'Store Credit',
        5 => 'Affirm (offline)',
        6 => 'Paypal (offline)',
        7 => 'Progressive Leasing (offline)',
    ];

    const MULTIPAY_SALE_ACTION = 1;
    const MULTIPAY_REFUND_ACTION = 2;
    const MULTIPAY_CANCEL_ACTION = 3;

    const STATE_QUOTE = 'quote';

    const CASH_TENDERED_DATA = 'multipay_cash_tendered';
    const CHANGE_DUE_DATA = 'multipay_change_due';
    const OPTION_PARTIAL_DATA = 'multipay_option_partial';
    const OPTION_TOTAL_DATA = 'multipay_option_total';
    const PAYMENT_METHOD_DATA = 'multipay_payment_method';
    const NEW_BALANCE_DATA = 'multipay_new_balance';

    const CC_NUMBER_DATA = 'multipay_cc_number';
    const EXP_MONTH_DATA = 'multipay_cc_exp_month';
    const EXP_YEAR_DATA = 'multipay_cc_exp_year';
    const CVV_NUMBER_DATA = 'multipay_cvv_number';

    const AMOUNT_DUE_DATA = 'multipay_amount_due';
    const PAYMENT_METHOD_NONCE = 'payment_method_nonce';

    const MULTIPAY_PAYMENT_DATA = [
        self::CASH_TENDERED_DATA,
        self::CHANGE_DUE_DATA,
        self::OPTION_PARTIAL_DATA,
        self::OPTION_TOTAL_DATA,
        self::PAYMENT_METHOD_DATA,
        self::PAYMENT_METHOD_NONCE,
        self::NEW_BALANCE_DATA,
        self::AMOUNT_DUE_DATA,
        self::CC_NUMBER_DATA,
        self::EXP_MONTH_DATA,
        self::EXP_YEAR_DATA,
        self::CVV_NUMBER_DATA,
    ];

    const CLIENT_XML = 'diamondnexus_multipay/general/client_id';
}
