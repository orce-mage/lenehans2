/**
 * Default Checkout Payment View Mixin
 */

define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Amasty_StorePickupWithLocator/js/model/pickup'
], function (ko, quote, pickup) {
    'use strict';

    return function (paymentDefault) {
        return paymentDefault.extend({
            isPlaceOrderActionAllowed: ko.pureComputed({
                read: function () {
                    return quote.billingAddress() !== null && pickup.isPickupValidOrIsNotPickup();
                },
                write: function () {}
            })
        });
    };
});
