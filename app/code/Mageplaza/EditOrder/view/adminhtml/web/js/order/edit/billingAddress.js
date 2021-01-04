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

    $.widget('mageplaza.mpEditBillingAddress', $.mageplaza.mpEditOrder, {
        params: {
            prefix: 'billing_address',
            editBtn: '#mpeditorder-billing-address-btn',
            locateBtn: '.order-billing-address .admin__page-section-item-title',
            popup: '#mpeditorder-billing-address',
            popupTitle: 'Edit Billing Address Information',
            errorMessage: '.mpeditorder-billing-address-error'
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            this.updateBillingAddress(res.billing_address);
        }
    });

    return $.mageplaza.mpEditOrderAddress;
});

