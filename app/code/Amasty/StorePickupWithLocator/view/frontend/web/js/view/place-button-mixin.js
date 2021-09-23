/**
 * Amasty OSC Place Order Button View Mixin
 */

define([
    'ko',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_Checkout/js/model/payment/payment-loading',
    'Amasty_Checkout/js/model/address-form-state',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (ko, pickup, paymentLoader, addressFormState, pickupDataResolver) {
    'use strict';

    return function (amPlaceButton) {
        return amPlaceButton.extend({
            isPlaceOrderActionAllowed: ko.pureComputed({
                read: function () {
                    var isStorePickupValid = true,
                        pickupDataStores = pickupDataResolver.pickupData().stores,
                        isStoresExist = pickupDataStores && pickupDataStores.length;

                    if (pickup.isPickup()) {
                        isStorePickupValid = Boolean(isStoresExist);
                    }

                    return !paymentLoader()
                        && !addressFormState.isBillingFormVisible()
                        && !addressFormState.isShippingFormVisible()
                        && isStorePickupValid;
                },
                write: function () {}
            })
        });
    };
});
