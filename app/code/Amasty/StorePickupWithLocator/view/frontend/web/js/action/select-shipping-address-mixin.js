define([
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup'
], function (wrapper, pickup) {
    'use strict';

    /**
     * Changing shipping address for Store Pickup shipping method only if shipping address type belongs to Store Pickup
     */
    return function (selectShippingAddressAction) {
        return wrapper.wrap(selectShippingAddressAction, function (original, shippingAddress) {
            var isPickupMethod = pickup.isPickup(),
                isNotPickupAddress = shippingAddress.getType() !== 'store-pickup-address';

            if (!(isPickupMethod && isNotPickupAddress)) {
                original(shippingAddress);
            }
        });
    };
});
