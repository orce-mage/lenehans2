/**
 * MSI locations model
 */

define([
    'jquery',
    'ko',
    'mage/storage',
    'Amasty_StorePickupWithLocatorMSI/js/action/locations-loader',
    'Amasty_StorePickupWithLocatorMSI/js/model/url-builder',
    'Amasty_StorePickupWithLocatorMSI/js/model/locations-map'
], function ($, ko, storage, locationsLoaderActions, urlBuilder, locationsMapModel) {
    'use strict';

    return {
        msiLocations: ko.observableArray(),

        /**
         * @param {String} productId
         * @return {void}
         */
        getMsiLocationsByProductId: function (productId) {
            var locationsUrlForCreation = '/amstorepickup_msi/:productId/getLocationsByProduct',
                msiLocationsApiUrl;

            if (!productId) {
                return;
            }

            msiLocationsApiUrl = urlBuilder.createUrl(locationsUrlForCreation, {
                productId: productId
            });

            locationsLoaderActions.show();

            storage
                .get(msiLocationsApiUrl)
                .success(function (response) {
                    if (!response.items) {
                        return;
                    }

                    this.setMsiLocations(response.items);

                    if (response.items.length) {
                        locationsMapModel.getLocationsMap(productId);
                    }
                }.bind(this))
                .fail(function (response) {
                    console.log(response);
                })
                .always(function () {
                    locationsLoaderActions.hide();
                });
        },

        /**
         * @param {Array} locations
         * @returns {void}
         */
        setMsiLocations: function (locations) {
            this.msiLocations(locations);
        }
    };
});
