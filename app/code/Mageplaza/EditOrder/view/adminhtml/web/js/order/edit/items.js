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

    $.widget('mageplaza.mpEditOrderItems', $.mageplaza.mpEditOrder, {
        params: {
            editBtn: '#mpeditorder-items-btn',
            locateBtn: 'section.admin__page-section .admin__page-section-title',
            popup: '#mpeditorder-items',
            form: '#edit_form',
            popupTitle: 'Edit Items Ordered',
            errorMessage: '.mpeditorder-items'
        },

        /**
         * @inheritDoc
         */
        setLocate: function () {
            var title = $('table.edit-order-table').parent().parent().find('.admin__page-section-title');

            $(this.params.editBtn).show().appendTo(title);
        },

        /**
         * @inheritDoc
         */
        updateData: function (res) {
            this.updateItems(res.items);
        }

    });

    return $.mageplaza.mpEditOrderItems;
});

