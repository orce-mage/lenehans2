/**
 * Toggle locations block action
 */

define([
    'jquery',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, productConfig) {
    'use strict';

    var selectors = {
            locationsContainer: '[data-ampickupmsi-js="locations-container"]',
            linkWrapper: '[data-ampickupmsi-js="link-wrapper"]'
        },

        /**
         * @param {Array} locations
         * @returns {void}
         */
        toggleLocationsBlock = function (locations) {
            var state = true;

            if (!!productConfig.productId() && !locations.length) {
                state = false;
            }

            $(selectors.locationsContainer).toggle(state);
            $(selectors.linkWrapper).toggle(!state);
        };

    return toggleLocationsBlock;
});
