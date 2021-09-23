/**
 * Main Pickup Store UIElement
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/form/element/select',
    'Magento_Customer/js/customer-data',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'mage/translate'
], function (
    $,
    quote,
    Select,
    customerData,
    pickupDataResolver,
    pickup
) {
    'use strict';

    return Select.extend({
        defaults: {
            value: '',
            caption: $.mage.__('Choose a store...'),
            storesSectionName: 'amasty-storepickup-data',
            selectedStoreSectionName: 'amasty-selected-pickup-info',
            template: 'Amasty_StorePickupWithLocator/pickup/pickup-store'
        },

        initConfig: function () {
            var stores,
                pickupData,
                amPickupConfig;

            this._super();

            pickupData = pickupDataResolver.pickupData;
            stores = pickupData().stores;
            amPickupConfig = window.checkoutConfig.amastyStorePickupConfig;

            if (stores
                && (pickupData().website_id !== amPickupConfig.websiteId
                    || pickupData().store_id !== amPickupConfig.storeId)
            ) {
                customerData.reload([ this.storesSectionName ], false);
            }

            this.options = stores || [];
            this.value = pickupDataResolver.getDataByKey('am_pickup_store');
            this.visible = pickup.isPickup();

            return this;
        },

        initObservable: function () {
            this.observe('options');

            pickupDataResolver.pickupData.subscribe(function (data) {
                this.options(data.stores);
            }, this);

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            pickupDataResolver.storeId.subscribe(this.storeObserver, this);

            this._super();

            return this;
        },

        initialize: function () {
            this._super();

            if (pickupDataResolver.storeId() && pickup.isPickup()) {
                pickupDataResolver.storeId.valueHasMutated();
            }

            return this;
        },

        /**
         * @param {Number} storeId
         * @returns {void}
         */
        storeObserver: function (storeId) {
            if (storeId && +this.value() !== +storeId) {
                this.value(String(storeId));
            }
        },

        onChangeStore: function (storeId) {
            pickupDataResolver.storeId(storeId);
        },

        /**
         * @param {Boolean} isActive
         * @returns {void}
         */
        pickupStateObserver: function (isActive) {
            if (isActive) {
                pickupDataResolver.storeId.valueHasMutated();
            }
        },

        openMap: function () {
            this.source.trigger('amStorepickup.data.openMap');
        }
    });
});
