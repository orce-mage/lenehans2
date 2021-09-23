/**
 * Amasty Store Locator main.js mixin
 */

define([
    'jquery',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, productConfigModel) {
    'use strict';

    return function (amLocator) {
        $.widget('mage.amLocator', amLocator, {
            collectParams: function (sortByDistance, isReset) {
                var productId = productConfigModel.productId()
                    ? productConfigModel.productId()
                    : this.options.productId;

                return {
                    'lat': this.latitude,
                    'lng': this.longitude,
                    'radius': this.getRadius(isReset),
                    'product': productId,
                    'category': this.options.categoryId,
                    'attributes': this.mapContainer.find(this.selectors.attributeForm).serializeArray(),
                    'sortByDistance': sortByDistance
                };
            }
        });

        return $.mage.amLocator;
    };
});
