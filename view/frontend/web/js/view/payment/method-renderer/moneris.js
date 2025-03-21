/**
 * Copyright © Magecan, Inc. All rights reserved.
 */

define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Vault/js/view/payment/vault-enabler'
], function ($, urlBuilder, errorProcessor, Component, quote, fullScreenLoader, redirectOnSuccessAction, vaultEnabler) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magecan_Moneris/payment/moneris',
            code: 'moneris',
            checkout: null,
            ticket: null
        },

        initialize: function () {
            var self = this;

            this._super();
            this.vaultEnabler = new vaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            var monerisLibraryUrl;
            if (window.checkoutConfig.payment.moneris.environment == 'prod') {
                monerisLibraryUrl = 'https://gateway.moneris.com/chktv2/js/chkt_v2.00.js';
            } else {
                monerisLibraryUrl = 'https://gatewayt.moneris.com/chktv2/js/chkt_v2.00.js';
            }

            $.getScript(monerisLibraryUrl, function () {
                self.initalizeCheckout();
            });
        },

        initalizeCheckout: function () {
            this.checkout = new monerisCheckout();
            this.checkout.setMode(window.checkoutConfig.payment.moneris.environment);
            this.checkout.setCheckoutDiv('monerisCheckout');

            const callback = this.callback.bind(this);
            this.checkout.setCallback('page_loaded', callback);
            this.checkout.setCallback('cancel_transaction', callback);
            this.checkout.setCallback('error_event', callback);
            this.checkout.setCallback('payment_receipt', callback);
            this.checkout.setCallback('payment_complete', callback);
            this.checkout.setCallback('payment_submitted', callback);
            this.checkout.setCallback('page_closed', callback);
        },

        callback: function (data) {
            data = JSON.parse(data);

            if (data.ticket != this.ticket || data.response_code != '001') {
                return;
            }

            switch (data.handler) {
                case 'page_loaded':
                    $('#moneris-actions-toolbar').hide();
                    $('#outerDiv').height(window.checkoutConfig.payment.moneris.iframeHeight).show();
                    fullScreenLoader.stopLoader();
                    break;
                case 'cancel_transaction':
                    $('#moneris-actions-toolbar').show();
                    $('#outerDiv').hide();
                    this.checkout.closeCheckout(data.ticket);
                    break;
                case 'payment_receipt':
                case 'payment_complete':
                    this.placeOrder();
                    break;
                case 'error_event':
                case 'payment_submitted':
                case 'page_closed':
                default:
                    break;
            }
        },

        preload: function (data, event) {
            var self = this;
            var payload = {billingAddress: quote.billingAddress()};

            fullScreenLoader.startLoader();

            $.ajax({
                url: urlBuilder.build('moneris/payment/preload'),
                data: JSON.parse(JSON.stringify(payload)),
                type: 'post',
                dataType: 'json'
            }).fail(
                function (response) {
                    errorProcessor.process(response, self.messageContainer);
                    fullScreenLoader.stopLoader();
                }
            ).done(
                function (response) {
                    self.ticket = response.ticket;
                    self.checkout.startCheckout(self.ticket);
                }
            )
        },

        /**
         * Place order.
         */
        placeOrder: function () {
            var self = this;

            this.getPlaceOrderDeferredObject()
                .always(
                    function () {
                        $('#outerDiv').hide();
                        self.checkout.closeCheckout(self.ticket);
                    }
                ).done(
                    function () {
                        redirectOnSuccessAction.execute();
                    }
                ).fail(
                    function () {
                        $('#moneris-actions-toolbar').show();
                    }
                );

            return true;
        },

        getCode: function () {
           return this.code;
        },

        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'ticket': this.ticket
                }
            };

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * @returns {Boolean}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * Returns vault code.
         *
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
        }
    });
});
