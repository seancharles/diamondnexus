/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com).
 * Modified by Prog Leasing, LLC. Copyright (c) 2018, Prog Leasing, LLC.
 */

/*jshint jquery:true*/
define([
    "jquery",
    "mage/translate",
    "Magento_Checkout/js/model/full-screen-loader",
    "Magento_Checkout/js/model/quote",
    "Magento_Checkout/js/model/url-builder",
    "Magento_Customer/js/model/customer",
    "Progressive_PayWithProgressive/js/model/progressive",
    "Magento_Ui/js/model/messageList"
], function ($, $t, fullScreenLoader, quote, urlBuilder, customer, progressiveCheckout, Messages) {

    var options = {
        demo_api_url: window.checkoutConfig.payment['progressive_gateway'].demo_api_url,
        production_api_url: window.checkoutConfig.payment['progressive_gateway'].production_api_url,
        mode: window.checkoutConfig.payment['progressive_gateway'].mode,
        tax_exempt: window.checkoutConfig.payment['progressive_gateway'].tax_exempt
};

    function buildRequestObject(checkoutObject) {

        function buildSessionData(data, target) {
            // if this is going into a PUT request. this is not needed if
            // this is going into a POST request
            target.sessionId = data.metadata.session_id;
            target.compositeId = data.metadata.composite_id;

            if (data.shipping) {
                target.customerEmail = data.shipping.email;
            }
        }

        // This data isn't currently used in our API yet. but it is good to start
        // getting in the habit of storing it.
        function buildBillingAddress(data, target) {
            target.billingAddress = target.billingAddress || {};

            if (data.billing) {
                target.email = data.billing.email;
                target.phone = data.billing.phone;

                if (data.billing.address) {
                    target.streetAddress = data.billing.address.line1;
                    target.streetAddress2 = data.billing.address.line2;
                    target.postalCode = data.billing.address.zipcode;
                    target.city = data.billing.address.city;
                    target.region = data.billing.address.state;
                    target.country = data.billing.address.country;
                }
            }
        }

        function getTaxAmount(data, target) {
            if (options.tax_exempt === "0")
                return data.tax_amount;
            return 0;
        }

        function buildOrderData(data, target) {
            target.orderAmount = data.total_amount;
            target.orderTaxAmount = getTaxAmount(data, target);
        }

        function buildShippingItem(data, target) {
            if (data.shipping_amount <= 0)
                return;
            var shippingItem = {
                type: null,
                sku: 0,
                name: "Shipping",
                quantity: 1,
                quantityUnit: null,
		used: false,
		itemStatus: "Delivery",
                unitPrice: data.shipping_amount,
                totalAmount: data.shipping_amount,
                totalDiscountAmount: 0,
                totalTaxAmount: 0,
                productUrl: null,
                imageUrl: null
            };
            target.orderLines.push(shippingItem);
        }

        function buildOrderItems(data, target) {
            if (data.items && data.items.length) {
                target.orderLines = target.orderLines || [];
                for (var i = 0; i < data.items.length; i++) {
                    var item = data.items[i];
                    var orderLine = {
                        type: null,
                        sku: item.sku,
                        name: item.display_name,
                        quantity: item.qty,
                        quantityUnit: null,
                        unitPrice: item.unit_price,
                        used: item.used,
			itemStatus: item.used? "Used" : "New",
                        totalAmount: (item.unit_price * item.qty) - item.discount,
                        totalDiscountAmount: item.discount, //Does magento apply discounts?
                        totalTaxAmount: item.tax_amount,
                        productUrl: item.item_url,
                        imageUrl: item.item_image_url
                    };
                    target.orderLines.push(orderLine);
                }
            }
        }

       
        var request = {};

        buildSessionData(checkoutObject, request);
        buildBillingAddress(checkoutObject, request);
        buildOrderData(checkoutObject, request);
        buildOrderItems(checkoutObject, request);
        buildShippingItem(checkoutObject, request);

        return request;
    }

    function getApiUrl() {
        if (options.mode.toLowerCase() === 'demo') {
            return options.demo_api_url;
        } else {
            return options.production_api_url;
        }
    }

    function updateSessionRequest(baseApiUrl, requestObject, successCallback, errorCallback) {

        return $.ajax({
            accepts: 'application/json',
            contentType: 'application/json',
            url: baseApiUrl + '/session',
            method: 'PUT',
            data: JSON.stringify(requestObject),
            success: onSuccess(),
            error: onError()
        });
    }

    function onSuccess(data) {
        //TODO: handle response data
    }

    function onError(err) {
        //TODO: handle error
    }

    return function (response) {
        fullScreenLoader.startLoader();

        var result = JSON.parse(response), checkoutObj;

        var successfulCheckoutUrl = result['metadata']['base_url'] + "/progressive/payment/confirm";
        var completeUrl = result['metadata']['base_url'] + "/progressive/payment/finalize";
        var thisUrl = result['metadata']['base_url'] + "/checkout/#payment";
        var sessionId = result['metadata']['session_id'];
        var compositeId = result['metadata']['composite_id'];

        progressiveCheckout.prepareOrderData(result);
        try {
            checkoutObj = progressiveCheckout.getData();
            if (checkoutObj.invalid_items.length > 0) {
                fullScreenLoader.stopLoader();
                let varItems = "";
                for (var i = 0; i < checkoutObj.invalid_items.length; ++i)
                    varItems += checkoutObj.invalid_items[i].display_name + (i+1 < checkoutObj.invalid_items.length ? "," : "");
                Messages.addErrorMessage({ 'message': $t(varItems + " may not be leased with Progressive Leasing")});
                return;
            }

            var responseObj = buildRequestObject(checkoutObj);

            $.when(updateSessionRequest(getApiUrl(), responseObj))
                .done(function (response) {
                    ProgLeasing.OpenApplicationUI(sessionId, successfulCheckoutUrl, thisUrl, compositeId, completeUrl, false);
                    })
                .fail(function (response) {
                    fullScreenLoader.stopLoader();
                    Messages.addErrorMessage({
                    'message': $t('Checkout with Progressive Leasing isn\'t available right now, please try again later.')
                    });
            })
        }
        finally {
            fullScreenLoader.stopLoader();
        }

    }
});
