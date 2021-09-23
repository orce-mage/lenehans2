define([
    'jquery',
    'uiComponent',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'mage/storage',
    'mage/loader'
], function ($, Component, pickupDataResolver, storage) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: false,
            ajaxUrl: 'amstorepickup/map/open',
            mapIsLoaded: false,
            locationsMap: '',
            pickupMapButton: '[data-ampickup-js="choose-store"]',
            listens: {
                '${ $.provider }:amStorepickup.data.openMap': 'openMap'
            }
        },

        initObservable: function () {
            this._super()
                .observe('visible mapIsLoaded');

            return this;
        },

        openMap: function () {
            if (!this.mapIsLoaded()) {
                $('body').trigger('processStart');
                storage
                    .get(this.ajaxUrl, true)
                    .success(function (response) {
                        this.locationsMap = response;
                        this.mapIsLoaded(true);
                        this.bindMapListeners();
                        this.visible(true);
                    }.bind(this))
                    .always(function () {
                        $('body').trigger('processStop');
                    });
            } else {
                this.visible(!this.visible());
            }
        },

        hidePopup: function () {
            this.visible(false);
        },

        bindMapListeners: function () {
            $(document).on('click', this.pickupMapButton, function (event) {
                event.preventDefault();
                pickupDataResolver.storeId(+$(event.target).data('ampickupLocation'));
                this.visible(false);
            }.bind(this));
        }
    });
});
