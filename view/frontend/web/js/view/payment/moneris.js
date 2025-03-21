/**
 * Copyright © Magecan, Inc. All rights reserved.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
],
function (Component, rendererList) {
    'use strict';

    if (window.checkoutConfig.payment.moneris.active) {
        rendererList.push({
            type: 'moneris',
            component: 'Magecan_Moneris/js/view/payment/method-renderer/moneris'
        });
    }

    return Component.extend({});
});
