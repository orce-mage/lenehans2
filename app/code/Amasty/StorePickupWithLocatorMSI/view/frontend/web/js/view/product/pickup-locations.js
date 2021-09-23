/**
 * PDP Pickup locations view
 */

define([
    'ko',
    'jquery',
    'underscore',
    'uiElement',
    'Amasty_StorePickupWithLocatorMSI/js/action/toggle-locations-block',
    'Amasty_StorePickupWithLocatorMSI/js/action/locations-map',
    'Amasty_StorePickupWithLocatorMSI/js/model/url-builder',
    'Amasty_StorePickupWithLocatorMSI/js/model/locations-map',
    'Amasty_StorePickupWithLocatorMSI/js/model/msi-locations',
    'Amasty_StorePickupWithLocatorMSI/js/model/product-config',
    'loader'
], function (
    ko,
    $,
    _,
    Element,
    toggleLocationsBlockAction,
    locationsMapActions,
    urlBuilder,
    locationsMapModel,
    msiLocationsModel,
    productConfig
) {
    'use strict';

    return Element.extend({
        defaults: {
            locations: [],
            expandedState: false,
            maxLocationsToShowBeforeExpand: 3,
            curbsideLabelEnabled: false,
            curbsideLabel: '',
            selectors: {
                addToCartForm: 'form#product_addtocart_form',
                locationsContainer: '[data-ampickupmsi-js="locations-container"]',
                locationsWrapper: '[data-ampickupmsi-js="locations-wrapper"]',
                locationsExpander: '[data-ampickup-js="locations-expander"]'
            },
            nodes: {
                addToCartForm: null,
                locationsContainer: null
            }
        },

        initialize: function () {
            this._super();

            this._initNodes();

            return this;
        },

        initObservable: function () {
            this._super().observe([ 'expandedState' ]);

            this.locations = msiLocationsModel.msiLocations;
            this.isConfigurable = productConfig.isConfigurable;
            this.productId = productConfig.productId;
            this.locations.subscribe(function (locations) {
                toggleLocationsBlockAction(locations);
            });

            return this;
        },

        initStatefull: function () {
            this._super();

            urlBuilder.storeCode = this.storeCode;

            return this;
        },

        _initNodes: function () {
            this.nodes.addToCartForm = $(this.selectors.addToCartForm);
            this.nodes.locationsContainer = $(this.selectors.locationsContainer);
        },

        toggleExpandedState: function () {
            this.expandedState(!this.expandedState());
        },

        toggleGradient: function () {
            var locationsWrapper = $(this.selectors.locationsWrapper),
                locationsWrapperHeight = locationsWrapper.height(),
                locationsWrapperScrollTop = locationsWrapper.scrollTop(),
                locationsWrapperScrollHeight = locationsWrapper[0].scrollHeight;

            $(this.selectors.locationsExpander).toggleClass(
                '-gradient',
                locationsWrapperScrollHeight - locationsWrapperHeight !== locationsWrapperScrollTop
            );
        },

        setSelectedLocationId: function () {
            var msiLocation = this;

            locationsMapModel.selectedLocationId(+msiLocation.location.id);
            locationsMapActions.selectLocationOnMap();
        }
    });
});
