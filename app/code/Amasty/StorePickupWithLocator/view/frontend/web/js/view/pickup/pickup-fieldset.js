/**
 * Main Pickup Fieldset UIComponent
 */
define([
    'ko',
    'uiCollection',
    'Magento_Checkout/js/model/quote',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (ko, Component, quote, pickup, pickupDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_StorePickupWithLocator/pickup/pickup-fieldset',
            stores: ko.observableArray([])
        },

        initialize: function () {
            this._super();

            quote.shippingMethod.subscribe(this.onShippingMethodChange, this);
            this.pickupData = pickupDataResolver.pickupData;

            return this;
        },

        onShippingMethodChange: function (method) {
            var isStorePickup = method
                && method['carrier_code'] === 'amstorepickup';

            pickup.isPickup(Boolean(isStorePickup));
        }
    });
});
