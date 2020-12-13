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
    'jquery'
], function ($) {
    'use strict';

    $.widget('mageplaza.mpEditOrder', {
        /**
         * @inheritDoc
         */
        _create: function () {
            this.selectMethod();
            this.updateShippingFee();
        },

        /**
         * calculate total fee
         */
        updateShippingFee: function () {
            var input = $('.mpeditorder-shipping-data-row input');

            input.on('change', function () {
                var el         = $(this),
                    parent     = $(this).parent().parent(),
                    fee        = parseFloat(parent.find('.shipping-fee').val()),
                    tax        = parseFloat(parent.find('.shipping-tax').val()),
                    discount   = parseFloat(parent.find('.shipping-discount').val()),
                    total      = fee + (fee * tax)/ 100 - discount,
                    emptyPrice = 0;

                if (!el.val()) {
                    el.val(emptyPrice.toFixed(2));
                }

                if (total < 0) {
                    total = emptyPrice;
                }

                parent.find('.shipping-total-fee').val(total.toFixed(2));
            });
        },

        /**
         * select shipping method option
         */
        selectMethod: function () {
            var methodInput = $('.mpeditorder-shipping-method-option input[name="order[shipping_method]"]'),
                table       = $('.mpeditorder-shipping-method-option table');

            methodInput.each(function () {
                var el     = $(this),
                    parent = el.parent();

                if (el.is(':checked')) {
                    parent.find('table').show();
                }

                parent.find('input[name="order[shipping_method]"], label').on('click', function () {
                    table.hide();
                    parent.find('table').show();
                });
            });
        }
    });

    return $.mageplaza.mpEditOrder;
});

