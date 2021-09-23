/**
 * Locations map model
 */

define([
    'jquery',
    'ko',
    'mage/storage'
], function ($, ko, storage) {
    'use strict';

    return {
        selectedLocationId: ko.observable(),
        mapLoadingInProgress: ko.observable(false),
        mapPopupState: ko.observable(false),
        locationsMapHtml: ko.observable(),
        mapIsLoaded: ko.observable(false),

        /**
         * @param {String} productId
         * @param {Boolean} showPopupAfterLoad
         * @returns {void}
         */
        getLocationsMap: function (productId, showPopupAfterLoad) {
            var getLocationsMapUrl = 'amstorepickupmsi/map/open/?product=' + productId;

            this.mapLoadingInProgress(true);
            this.mapIsLoaded(false);

            if (showPopupAfterLoad) {
                $('body').trigger('processStart');
            }

            storage
                .get(getLocationsMapUrl)
                .success(function (response) {
                    this.setLocationsMapHtml(response);
                    this.mapIsLoaded(true);

                    if (showPopupAfterLoad) {
                        this.mapPopupState(true);
                    }
                }.bind(this))
                .fail(function (response) {
                    console.log(response);
                })
                .always(function () {
                    this.mapLoadingInProgress(false);
                }.bind(this));
        },

        /**
         * @param {String} locationsMap
         * @returns {void}
         */
        setLocationsMapHtml: function (locationsMap) {
            this.locationsMapHtml(locationsMap);
        }
    };
});
