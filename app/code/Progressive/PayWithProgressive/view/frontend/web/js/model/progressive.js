/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jsquery:true*/
define([
    "underscore",
    "Magento_Checkout/js/model/quote",
    "mage/url",
    "Magento_Customer/js/model/customer"
], function (_, quote, url, customer) {
    'use strict';
    return {
        items: [],
        invalid_items: [],
        order_id: null,
        shipping_amount: null,
        tax_amount: null,
        total_amount: null,
        shipping: null,
        billing: null,
        discounts: null,
        metadata: null,

        /**
         * Get checkout data
         */
        getData: function () {
            var _self = this;
            this.prepareItems();
            this.prepareTotals();
            //this.prepareTotals();
            this.initMetadata();
            return {
                items: _self.items,
                invalid_items: _self.invalid_items,
                order_id: _self.order_id,
                shipping_amount: _self.shipping_amount,
                tax_amount: _self.tax_amount,
                total_amount: _self.total,
                shipping: _self.prepareAddress('shipping'),
                billing: _self.prepareAddress('billing'),
                discounts: _self.discounts,
                metadata: _self.metadata
            }
        },

        /**
         * prepare items data
         */
        prepareItems: function () {
            var quoteItems = quote.getItems();
            this.items.length = 0;
            this.invalid_items.length = 0;
            for (var i = 0; i < quoteItems.length; i++) {
                var newItem = 
                {
                    display_name: quoteItems[i].name,
                    sku: quoteItems[i].sku,
                    unit_price: parseFloat(quoteItems[i].price),
                    discount: parseFloat(quoteItems[i].discount_amount),
                    qty: quoteItems[i].qty,
                    used: quoteItems[i].is_used === "Yes",
                    leasable: quoteItems[i].is_leasable !== "No",
                    item_image_url: quoteItems[i].thumbnail,
                    item_url: (quoteItems[i].product.request_path) ?
                        url.build(quoteItems[i].product.request_path) : quoteItems[i].thumbnail
                };

                this.items.push(newItem);
                if (!newItem.leasable)
                    this.invalid_items.push(newItem);
            }
        },

        /**
         * init metadata
         */
        initMetadata: function () {
            if (!this.metadata.shipping_type && quote.shippingMethod()) {
                this.metadata.shipping_type =
                    quote.shippingMethod().carrier_title + ' - ' + quote.shippingMethod().method_title;
            }
        },

        /**
         * Set order id
         *
         * @param orderId
         */
        setOrderId: function (orderId) {
            if (orderId) {
                this.orderId = orderId;
            }
        },

        /**
         * Prepare totals
         */
        prepareTotals: function () {
            var totals = quote.getTotals();
            this.shipping_amount = totals().shipping_amount;
            this.total = totals().subtotal_with_discount + totals().shipping_amount;
            this.tax_amount = totals().tax_amount;
        },

        /**
         * Prepare Address
         *
         * @param type
         * @returns {{}}
         */
        prepareAddress: function (type) {
            var name, address, fullname, street, result = {};
            if (type === 'shipping') {
                address = quote.shippingAddress();
            } else if (type === 'billing') {
                address = quote.billingAddress();
            }
            if (address.lastname) {
                fullname = address.firstname + ' ' + address.lastname;
            } else {
                fullname = address.firstname;
            }
            name = {
                "full": fullname
            };
            if (address.street[0]) {
                street = address.street[0];
            }
            result["address"] = {
                "line1": street,
                "city": address.city,
                "state": address.regionCode,
                "zipcode": address.postcode,
                "country": address.countryId
            };
            result["name"] = name;
            if (address.street[1]) {
                result.address.line2 = address.street[1];
            }
            if (address.telephone) {
                result.phone_number = address.telephone;
            }
            if (!customer.isLoggedIn()) {
                result.email = quote.guestEmail;
            } else if (customer.customerData.email) {
                result.email = customer.customerData.email;
            }
            return result;
        },

        /**
         * Specify order data
         *
         * @param data
         */
        prepareOrderData: function (data) {
            if (data.order_increment_id !== 'undefined') {
                this.order_id = data.order_increment_id;
            }
            if (data.order_amount !== 'undefined') {
                this.total_amount = data.order_amount;
            }
            if (data.tax_amount !== 'undefined') {
                this.tax_amount = data.tax_amount;
            }
            if (data.shipping_amount !== 'undefined') {
                this.shipping_amount = data.shipping_amount;
            }
            if (data.discounts) {
                this.setDiscounts(data.discounts);
            }
            if (data.metadata) {
                this.setMetadata(data.metadata);
            }
        },

        /**
         * Add items
         *
         * @param items
         */
        addItems: function (items) {
            if (items !== 'undefined') {
                this.items = _.union(this.items, items);
            }
        },

        /**
         * Specify discounts
         *
         * @param discounts
         */
        setDiscounts: function (discounts) {
            if (discounts) {
                this.discounts = discounts;
            }
        },

        /**
         * Specify metadata
         *
         * @param metadata
         */
        setMetadata: function (metadata) {
            if (metadata) {
                this.metadata = metadata;
            }
        }
    }
});
