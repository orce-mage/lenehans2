/**
 * PDP Pickup locations view
 */

define([
    'ko',
    'jquery',
    'underscore',
    'uiElement',
    'MidoriWeb_Custom/js/model/delivery-message',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config',
    'loader'
], function (
    ko,
    $,
    _,
    Element,
    deliveryMessageModel,
    productConfig
) {
    'use strict';

    return Element.extend({
        defaults: {
            deliveryMessage: ''
        },

        initialize: function () {
            this._super();

            return this;
        },

        initObservable: function () {
            this.deliveryMessage = deliveryMessageModel.deliveryMessage;
            this.isConfigurable = productConfig.isConfigurable;
            this.productId = productConfig.productId;

            return this;
        }
    });
});
