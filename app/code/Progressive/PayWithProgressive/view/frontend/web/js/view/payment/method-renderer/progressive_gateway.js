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

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/action/set-payment-information',
        'Progressive_PayWithProgressive/js/action/prepare-progressive-checkout',
        'Progressive_PayWithProgressive/js/action/send-to-progressive-checkout',
        'Progressive_PayWithProgressive/js/action/verify-progressive',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, Component, quote, additionalValidators,
              urlBuilder, errorProcessor, Messages, setPaymentAction,
              initCheckoutAction, sendToProgressiveCheckout, verifyProgressiveAction, customerData, fullScreenLoader) {

        return Component.extend({
            defaults: {
                template: 'Progressive_PayWithProgressive/payment/progressive_gateway',
                transactionResult: '',
                paymentMethodNonce: null
            },

            /**
             * Payment code
             *
             * @returns {string}
             */
            getCode: function () {
                return 'progressive_gateway';
            },

            /**
             * Get payment info
             *
             * @returns {info|*|indicators.info|z.info|Wd.$get.info|logLevel.info}
             */
            getInfo: function () {
               return window.checkoutConfig.payment['progressive_gateway'].info
            },

            /**
             * get api url from config (demo)
             *
             * @returns {string}
             */
            getApiUrl: function () {
                var mode = window.checkoutConfig.payment['progressive_gateway'].mode;
                if (mode.toLowerCase() === "demo") {
                    return window.checkoutConfig.payment['progressive_gateway'].demo_api_url;
                } else {
                    return window.checkoutConfig.payment['progressive_gateway'].production_api_url;
                }
            },

            /**
             * continue with Progressive Pay
             */
            continueWithProgressive: function () {
                var _self = this;
                fullScreenLoader.startLoader();

               if (additionalValidators.validate()) {
                   // update payment method if additional data was changed
                   this.selectPaymentMethod();
                   $.when(setPaymentAction(_self.messageContainer, {'method': _self.getCode()})).done(function () {
                       $.when(initCheckoutAction(_self.messageContainer)).done(function (response) {
                           customerData.invalidate(['cart']);
                           sendToProgressiveCheckout(response);
                       });
                   }).fail(function(){
                       _self.isPlaceOrderActionAllowed(true);
                       fullScreenLoader.stopLoader();
                   });
                   return false;
               }
            },

            /**
             * init payment
             */
            initialize: function () {
              var _self = this;
                this._super();
                var ecomScript = document.createElement('script');
                ecomScript.setAttribute('src', _self.getApiUrl() + 'content/ui');
                ecomScript.setAttribute('src_type', 'url');
                document.head.appendChild(ecomScript);
              $.when(verifyProgressiveAction(_self.messageContainer)).done(function (response) {
                 if (response) {
                     _self.selectPaymentMethod();
                 }
              }).fail(function (response) {
                 errorProcessor.process(response, _self.messageContainer);
              });
            }
        });
    }
);
