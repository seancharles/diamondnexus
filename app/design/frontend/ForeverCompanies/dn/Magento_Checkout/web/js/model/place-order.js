define(
    [
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/payment/place-order-hooks',
        'underscore'
    ],
    function (storage, errorProcessor, fullScreenLoader, customerData, hooks, _) {
        'use strict';

        return function (serviceUrl, payload, messageContainer) {
            var headers = {};

            fullScreenLoader.startLoader();
            _.each(hooks.requestModifiers, function (modifier) {
                modifier(headers, payload);
            });

            return storage.post(
                serviceUrl, JSON.stringify(payload), true, 'application/json', headers
            ).always(
                function (response) {
			
                    var clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
					}
                    fullScreenLoader.stopLoader();
                    _.each(hooks.afterRequestListeners, function (listener) {
                        listener();
                    });
                }
            );
        };
    }
);