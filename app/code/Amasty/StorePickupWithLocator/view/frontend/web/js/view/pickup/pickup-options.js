/**
 * Pickup Options Component
 */
define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'matchMedia',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (
    _,
    $,
    ko,
    Component,
    mediaCheck,
    pickup,
    pickupDataResolver
) {
    'use strict';

    var storeCurbsideEnabled = ko.observable();

    return Component.extend({
        defaults: {
            template: 'Amasty_StorePickupWithLocator/pickup/pickup-options',
            isCart: false,
            curbsideChecked: false,
            curbsideCommentValue: '',
            ignoreTmpls: {
                curbsideChecked: true
            },
            links: {
                curbsideChecked: '${ $.provider }:${ $.dataScope }.curbside_state'
            }
        },
        mediaBreakpoint: '(min-width: 768px)',
        visibleComputed: ko.pureComputed(function () {
            return Boolean(pickupDataResolver.storeId() && pickup.isPickup() && storeCurbsideEnabled());
        }),
        firstLoad: true,
        selectors: {
            curbsideConditions: '[data-ampickup-js="curbside-conditions"]'
        },

        initObservable: function () {
            this._super()
                .observe([
                    'visible',
                    'curbsideChecked',
                    'storeCurbsideConditions'
                ]);

            this.visibleComputed.subscribe(this.visible);
            pickupDataResolver.storeId.subscribe(this.onChangeStore, this);

            if (!pickupDataResolver.curbsideData()) {
                pickupDataResolver.curbsideData({});
            }

            return this;
        },

        initConfig: function () {
            var curbsideData;

            this._super();

            curbsideData = pickupDataResolver.curbsideData();
            this.visible = this.visibleComputed();
            this.curbsideConfig = window.checkoutConfig.amastyStorePickupConfig.curbsideConfig;
            this.curbsideChecked = curbsideData && Object.keys(curbsideData).length
                ? curbsideData.checkbox_state
                : false;

            return this;
        },

        _initNodes: function () {
            this.curbsideConditionsNode = $(this.selectors.curbsideConditions);
        },

        initCollapsible: function () {
            if (!this.curbsideConditionsNode) {
                this.curbsideConditionsNode = $(this.selectors.curbsideConditions);
            }

            this.curbsideConditionsNode.collapsible({
                'openedState': 'active'
            });

            if (this.isCart) {
                return;
            }

            mediaCheck({
                media: this.mediaBreakpoint,
                entry: function () {
                    this.curbsideConditionsNode.collapsible('activate');
                    this.curbsideConditionsNode.collapsible('option', 'collapsible', false);
                }.bind(this),
                exit: function () {
                    this.curbsideConditionsNode.collapsible('option', 'collapsible', true);
                    this.curbsideConditionsNode.collapsible('forceActivate');
                }.bind(this)
            });
        },

        getConditionsVisibility: function () {
            if (!this.curbsideConfig) {
                return false;
            }

            if (this.isCart) {
                return this.curbsideConfig.display_curbside_conditions;
            }

            if (!this.curbsideConfig.checkbox_enabled) {
                return this.curbsideConfig.display_curbside_conditions;
            }

            return this.curbsideChecked()
                && this.curbsideConfig.display_curbside_conditions;
        },

        onCurbsideChange: function () {
            pickupDataResolver.extendCurbsideData('checkbox_state', this.curbsideChecked());
        },

        onChangeStore: function () {
            this.selectedStore = pickupDataResolver.getCurrentStoreData();

            if (this.selectedStore) {
                storeCurbsideEnabled(this.selectedStore.curbside_enable);
                this.storeCurbsideConditions(this.selectedStore.curbside_conditions_text);
            }

            if (this.firstLoad === false) {
                this.curbsideChecked(false);
                pickupDataResolver.extendCurbsideData('checkbox_state', false);
            }
            if (this.firstLoad === true) {
                this.visible.valueHasMutated();
            }

            this.firstLoad = false;
        }
    });
});
