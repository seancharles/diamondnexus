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

    $.widget('mageplaza.mpEditOrderInfo', $.mageplaza.mpEditOrder, {
        params: {
            prefix: 'info',
            editBtn: '#mpeditorder-order-info-btn',
            locateBtn: '.order-information .admin__page-section-item-title',
            popup: '#mpeditorder-order-info',
            popupTitle: 'Edit Order Information',
            errorMessage: '.mpeditorder-info-error'
        },

        /**
         * @inheritDoc
         */
        updateForm: function () {
            this.editDateTime();
        },

        /**
         * @inheritDoc
         */
        updateData: function () {
            this.updateOrderInfo();
        }
    });

    return $.mageplaza.mpEditOrderInfo;
});

