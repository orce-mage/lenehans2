define([
    'underscore',
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Magento_Checkout/js/model/quote'
],function (_, wrapper, pickup, pickupDataResolver, quote) {
    'use strict';

    /**
     * Check is Pickup method available for location address and remove if it isn't
     *
     * @param {Array} ratesData
     */
    function checkPickupAvailability (ratesData) {
        var availableRate,
            isAvailable,
            shippingAddress = quote.shippingAddress();

        if (!shippingAddress || shippingAddress.getType() !== 'store-pickup-address') {
            return;
        }

        availableRate = _.find(ratesData, function (rate) {
            return rate['carrier_code'] === 'amstorepickup';
        });

        isAvailable = availableRate && availableRate['available'];

        if (!isAvailable) {
            pickupDataResolver.removeStore(shippingAddress.getLocationId());
        }
    }

    /**
     * Observe pickup address availability
     * mixin is more optimized then ko.subscribe for this case
     */
    return function (shippingService) {
        shippingService.setShippingRates = wrapper.wrapSuper(shippingService.setShippingRates, function (ratesData) {
            if (ratesData && ratesData.length && pickup.isPickup()) {
                checkPickupAvailability(ratesData);
            }

            return this._super();
        });

        return shippingService;
    };
});
