/**
 * PDP MSI locations map view
 */

define([
    'jquery',
    'uiElement',
    'Amasty_StorePickupWithLocatorMSI/js/action/locations-map',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config',
    'Amasty_StorePickupWithLocatorMSI/js/model/locations-map',
    'Amasty_Storelocator/js/model/states-storage'
], function ($, Element, locationsMapActions, productConfig, locationsMapModel, locatorStatesStorage) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: false,
            mapIsLoaded: false,
            locationsMapHtml: ''
        },

        initObservable: function () {
            this._super();

            this.visible = locationsMapModel.mapPopupState;
            this.mapIsLoaded = locationsMapModel.mapIsLoaded;
            this.locationsMapHtml = locationsMapModel.locationsMapHtml;

            locationsMapModel.mapLoadingInProgress.subscribe(function (state) {
                if (!state) {
                    $('body').trigger('processStop');
                }
            });

            locatorStatesStorage.storeListIsLoaded.subscribe(function (state) {
                if (state) {
                    locationsMapActions.selectLocationOnMap();
                }
            });

            return this;
        },

        hideMap: function () {
            locationsMapActions.hide();
        }
    });
});
