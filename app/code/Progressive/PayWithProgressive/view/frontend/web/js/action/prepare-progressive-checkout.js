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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (quote, urlBuilder, storage, errorProcessor, customer, getTotalsAction, fullScreenLoader) {
        'use strict';

        return function (messageContainer) {
            var serviceUrl;
            serviceUrl = urlBuilder.createUrl('/progressive/checkout/init', {});

            return storage.post(
                serviceUrl
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                }
            )
        };
    }
);