define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'custompayment',
                component: 'Vendor_Module/js/view/payment/method-renderer/multipay-method'
            }
        );
        return Component.extend({});
    }
);