define([
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup'
], function (wrapper, pickup) {
    'use strict';

    return function (klarnaKP) {
        //fix Klarna shipping address validation for pickup address

        klarnaKP.buildAddress = wrapper.wrap(klarnaKP.buildAddress, function (original) {
            var result = original();

            if (pickup.isPickup()) {
                if (!result.given_name) {
                    result.given_name = '-';
                }

                if (!result.family_name) {
                    result.family_name = '-';
                }
            }

            return result;
        });

        return klarnaKP;
    };
});
