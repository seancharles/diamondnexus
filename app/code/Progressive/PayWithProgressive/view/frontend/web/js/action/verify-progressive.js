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

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor'
],
    function ($, authenticationPopup, customerData, urlBuilder, storage, fullScreenLoader, errorProcessor) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl = urlBuilder.createUrl('/progressive/checkout/verify', {}), result;
            fullScreenLoader.startLoader();
            result = storage.post(serviceUrl).done(
                function (response) {
                   return response;
                }).fail(
                    function (response) {
                       errorProcessor.process(response, messageContainer);
                });
            fullScreenLoader.stopLoader();
            return result;
        };
    }

);