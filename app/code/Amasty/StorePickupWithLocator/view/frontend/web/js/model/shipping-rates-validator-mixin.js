define([
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup'
],function (wrapper, pickup) {
    'use strict';

    return function (target) {
        var mixin = {
            /**
             * Disable validation for store pickup shipping address
             */
            validateFields: function (original) {
                if (!pickup.isPickup()) {
                    original();
                }
            },
        };

        wrapper._extend(target, mixin);
        return target;
    };
});
