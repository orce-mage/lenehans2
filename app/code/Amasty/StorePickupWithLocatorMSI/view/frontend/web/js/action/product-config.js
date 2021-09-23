/**
 * Product config actions
 */

define([
    'jquery',
    'underscore',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config'
], function ($, _, productConfigModel) {
    'use strict';

    var selectors = {
        addToCartForm: 'form#product_addtocart_form',
        msiLocationsContainer: '[data-ampickupmsi-js="locations-container"]'
    };

    return {
        setConfigurableState: function () {
            var form = $(selectors.addToCartForm),
                configurableState;

            if (!form) {
                return;
            }

            configurableState = !!_.find(form.serializeArray(), function (item) {
                return item.name.indexOf('super_attribute') !== -1;
            });

            productConfigModel.isConfigurable(configurableState);
        },

        /**
         * @param {String} productId
         * @returns {void}
         */
        setProductId: function (productId) {
            productConfigModel.productId(productId);
        },

        setMsiEnabledState: function () {
            var msiLocationsContainer = $(selectors.msiLocationsContainer);

            productConfigModel.isMsiEnabled = !!msiLocationsContainer.length;
        }
    };
});
