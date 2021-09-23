define([
    'ko'
], function (ko) {
    'use strict';

    return {
        /**
         * Is Pickup method selected
         */
        isPickup: ko.observable(false),
        isPickupValidOrIsNotPickup: ko.observable(false)
    };
});
