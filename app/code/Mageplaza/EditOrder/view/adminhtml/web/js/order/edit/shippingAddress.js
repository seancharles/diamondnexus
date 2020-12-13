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

    $.widget('mageplaza.mpEditOrderAddress', $.mageplaza.mpEditOrder, {
        params: {
            prefix: 'shipping_address',
            editBtn: '#mpeditorder-shipping-address-btn',
            locateBtn: '.order-shipping-address .admin__page-section-item-title',
            popup: '#mpeditorder-shipping-address',
            popupTitle: 'Edit Shipping Address Information',
            errorMessage: '.mpeditorder-shipping-address-error'
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            this.updateShippingAddress(res.shipping_address);
        }
    });

    return $.mageplaza.mpEditOrderAddress;
});

