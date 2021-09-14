/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/view/summary/discount'
], function ($, _, Component, quote, discountView) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/shipping'
        },
        quoteIsVirtual: quote.isVirtual(),
        totals: quote.getTotals(),

        /**
         * @return {*}
         */
        getShippingMethodTitle: function () {
	
			var shippingMethodTitle = quote['totals']['_latestValue']['total_segments'][2]['title'];
			shippingMethodTitle = shippingMethodTitle.replace("Shipping & Handling (", "");
			shippingMethodTitle = shippingMethodTitle.replace(")", "");
			
            return shippingMethodTitle;
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getValue: function () {
			return this.getFormattedPrice(quote['totals']['_latestValue']['total_segments'][2]['value']);
        },

        /**
         * If is set coupon code, but there wasn't displayed discount view.
         *
         * @return {Boolean}
         */
        haveToShowCoupon: function () {
            var couponCode = this.totals()['coupon_code'];

            if (typeof couponCode === 'undefined') {
                couponCode = false;
            }

            return couponCode && !discountView().isDisplayed();
        },

        /**
         * Returns coupon code description.
         *
         * @return {String}
         */
        getCouponDescription: function () {
            if (!this.haveToShowCoupon()) {
                return '';
            }

            return '(' + this.totals()['coupon_code'] + ')';
        }
    });
});
