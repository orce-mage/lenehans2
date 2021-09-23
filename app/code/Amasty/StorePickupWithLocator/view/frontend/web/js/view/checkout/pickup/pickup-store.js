/**
 * Pickup Store UIElement for Checkout page
 * Nested from Main Pickup Store UIElement
 */
define([
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-store',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/shipping-address-service'
], function (PickupStore, pickupDataResolver, addressService) {
    'use strict';

    return PickupStore.extend({
        defaults: {
            visible: false,
            required: true,
            template: 'Amasty_StorePickupWithLocator/checkout/pickup/pickup-store'
        },

        storeObserver: function () {
            this._super();

            addressService.selectStoreAddress(pickupDataResolver.getCurrentStoreData());
        },

        pickupStateObserver: function (isActive) {
            this._super();

            if (!isActive) {
                addressService.resetAddress();
            }

            this.visible(isActive);
        }
    });
});
