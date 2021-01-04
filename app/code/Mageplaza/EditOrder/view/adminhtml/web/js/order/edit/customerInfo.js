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

    $.widget('mageplaza.mpEditOrderCustomer', $.mageplaza.mpEditOrder, {
        params: {
            prefix: 'customer',
            editBtn: '#mpeditorder-account-info-btn',
            locateBtn: '.order-account-information .admin__page-section-item-title',
            popup: '#mpeditorder-account-info',
            popupTitle: 'Edit Customer Information',
            errorMessage: '.mpeditorder-customer-error'
        },

        /**
         * @inheritDoc
         */
        updateForm: function () {
            this.dependent();
        },

        /**
         * @inheritDoc
         */
        updateData: function () {
            this.updateOrderCustomer();
        }
    });

    return $.mageplaza.mpEditOrderCustomer;
});

