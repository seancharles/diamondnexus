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

    $.widget('mageplaza.mpEditQuickEdit', $.mageplaza.mpEditOrder, {
        params: {
            editBtn: '#mpeditorder-quick-edit-button',
            popup: '#mpeditorder-quick-edit',
            popupTitle: 'Quick Edit Order',
            errorMessage: '.mpeditorder-quick-edit-error'
        },

        /**
         * @inheritDoc
         */
        updateForm: function () {
            this.editDateTime();
            this.dependent();
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            if (typeof res.info !== 'undefined') {
                this.updateOrderInfo();
            }
            if (typeof res.customer !== 'undefined') {
                this.updateOrderCustomer();
            }
            if (typeof res.shipping_address !== 'undefined') {
                this.updateShippingAddress(res.shipping_address);
            }
            if (typeof res.billing_address !== 'undefined') {
                this.updateBillingAddress(res.billing_address);
            }
            if (typeof res.items !== 'undefined') {
                this.updateItems(res.items);
            }
            if (typeof res.shipping_method !== 'undefined') {
                this.updateShippingMethod(res.shipping_method);
            }
            if (typeof res.payment_method !== 'undefined') {
                this.updatePaymentMethod(res.payment_method);
            }
        }
    });

    return $.mageplaza.mpEditQuickEdit;
});

