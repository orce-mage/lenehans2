/**
 * Locations map actions
 */

define([
    'jquery',
    'Amasty_StorePickupWithLocatorMSI/js/model/locations-map',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config',
    'Amasty_Storelocator/js/model/states-storage'
], function ($, locationsMapModel, productConfigModel, locatorStatesStorage) {
    'use strict';

    var selectors = {
        amLocatorStoreList: '[data-amlocator-js="store-list"]'
    };

    return {
        show: function () {
            if (locationsMapModel.mapLoadingInProgress()) {
                $('body').trigger('processStart');
            }

            if (locationsMapModel.mapIsLoaded()) {
                locationsMapModel.mapPopupState(true);
            }
        },

        hide: function () {
            locationsMapModel.mapPopupState(false);
        },

        selectLocationOnMap: function () {
            var locationId = locationsMapModel.selectedLocationId(),
                selectedLocationOnMap = $(selectors.amLocatorStoreList).find('[data-amid=' + locationId + ']');

            if (!locationId && locationId !== 0) {
                return;
            }

            if (!selectedLocationOnMap.length) {
                this.reloadLocationsMap();

                return;
            }

            if (!locationsMapModel.mapPopupState()) {
                this.show();
            }

            selectedLocationOnMap.click();
        },

        reloadLocationsMap: function () {
            locatorStatesStorage.storeListIsLoaded(false);
            locationsMapModel.getLocationsMap(productConfigModel.productId(), true);
        }
    };
});
