/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Mageplaza_EditOrder/js/order/edit'
], function ($) {
    'use strict';

    $.widget('mageplaza.mpEditOrderPaymentMethod', $.mageplaza.mpEditOrder, {
        params: {
            editBtn: '#mpeditorder-payment-method-btn',
            locateBtn: '.order-payment-method .admin__page-section-item-title',
            popup: '#mpeditorder-payment-method',
            popupTitle: 'Edit Payment Method',
            errorMessage: '.mpeditorder-payment-method-error'
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            this.updatePaymentMethod(res.payment_method);
        }
    });

    return $.mageplaza.mpEditOrderPaymentMethod;
});

