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

    $.widget('mageplaza.mpEditOrderShippingMethod', $.mageplaza.mpEditOrder, {
        params: {
            prefix: 'shipping_method',
            editBtn: '#mpeditorder-shipping-method-btn',
            locateBtn: '.order-shipping-method .admin__page-section-item-title',
            popup: '#mpeditorder-shipping-method',
            popupTitle: 'Edit Shipping Method',
            errorMessage: '.mpeditorder-shipping-method-error',
            isValid: true
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            this.updateShippingMethod(res.shipping_method);
        }
    });

    return $.mageplaza.mpEditOrderShippingMethod;
});

