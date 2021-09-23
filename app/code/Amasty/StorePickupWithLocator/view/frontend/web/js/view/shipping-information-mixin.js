define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'mage/translate'
], function ($, registry, quote, pickupDataResolver) {
    'use strict';

    return function (ShippingInformation) {
        return ShippingInformation.extend({
            getShippingMethodTitle: function () {
                var shippingMethodTitle = this._super(),
                    shippingMethod = quote.shippingMethod(),
                    dateComponent,
                    timeComponent,
                    pickupDateLabel,
                    pickupTimeLabel,
                    store,
                    curbsideLabel;

                if (shippingMethod && shippingMethod['carrier_code'] === 'amstorepickup') {
                    dateComponent = registry.get({ index: 'am_pickup_date' });
                    store = pickupDataResolver.getCurrentStoreData();
                    curbsideLabel = window.checkoutConfig.amastyStorePickupConfig.curbsideConfig.checkbox_label;

                    if (dateComponent) {
                        pickupDateLabel = $.mage.__('Pickup Date') + ': ' + dateComponent.shiftedValue();
                        timeComponent = registry.get({ index: 'am_pickup_time' });
                    }

                    if (timeComponent) {
                        pickupTimeLabel = $.mage.__('Pickup Time') + ': ' + timeComponent.pickupTimeLabel;
                    }

                    if (store && store.name) {
                        shippingMethodTitle = shippingMethodTitle.replace(shippingMethod['method_title'], store.name);
                    }

                    if (pickupDataResolver.curbsideData().checkbox_state === true) {
                        shippingMethodTitle = shippingMethodTitle.replace(
                            shippingMethod['carrier_title'],
                            shippingMethod['carrier_title'] + ' - ' + curbsideLabel
                        );
                    }

                    if (pickupDateLabel) {
                        shippingMethodTitle += ' (' + pickupDateLabel;
                    }

                    if (pickupTimeLabel) {
                        shippingMethodTitle += '; ' + pickupTimeLabel;
                    }

                    if (pickupDateLabel) {
                        shippingMethodTitle += ')';
                    }
                }

                return shippingMethodTitle;
            }
        });
    };
});
